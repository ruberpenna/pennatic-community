<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * Dumps the MySQL database to a gzip-compressed SQL file without needing mysqldump.
 *
 * One consistent InnoDB snapshot is read, so the site can stay online. Each statement ends with ";"
 * at the end of a line (values are escaped, so rows never span lines), which db:restore-sql relies on.
 */
class BackupDatabase extends Command
{
    protected $signature = 'db:backup-sql {path : Output file (.sql.gz)} {--connection= : Database connection, default connection if omitted}';

    protected $description = 'Dump the MySQL database to a gzip SQL file';

    /** Tables whose rows are throwaway (structure is still dumped). */
    private const SKIP_DATA = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

    private const ROWS_PER_INSERT = 200;

    public function handle(): int
    {
        $connection = DB::connection($this->option('connection') ?: null);

        if ($connection->getDriverName() !== 'mysql') {
            $this->error('Only MySQL connections are supported.');

            return self::FAILURE;
        }

        $pdo = $connection->getPdo();
        $path = $this->argument('path');
        $tmp = $path.'.part';
        $gz = gzopen($tmp, 'wb6');

        $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

        try {
            $database = $connection->getDatabaseName();
            gzwrite($gz, "-- Penna database backup of `{$database}` at ".now()->toIso8601String()."\n");
            gzwrite($gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
            $totalRows = 0;

            foreach ($tables as $table) {
                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM)[1];
                gzwrite($gz, "DROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n");

                if (in_array($table, self::SKIP_DATA, true)) {
                    continue;
                }

                $totalRows += $this->dumpRows($pdo, $gz, $table);
            }

            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
            $pdo->exec('COMMIT');
        } catch (\Throwable $e) {
            $pdo->exec('ROLLBACK');
            gzclose($gz);
            @unlink($tmp);

            throw $e;
        }

        gzclose($gz);
        rename($tmp, $path);

        $this->info(sprintf('Dumped %d tables, %d rows to %s (%s KB)', count($tables), $totalRows, $path, round(filesize($path) / 1024)));

        return self::SUCCESS;
    }

    /**
     * @param  resource  $gz
     */
    private function dumpRows(PDO $pdo, $gz, string $table): int
    {
        // Stream rows instead of loading the whole table into memory.
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $statement = $pdo->query("SELECT * FROM `{$table}`", PDO::FETCH_NUM);
        $columns = null;
        $batch = [];
        $count = 0;

        while ($row = $statement->fetch()) {
            if ($columns === null) {
                $columns = '`'.implode('`, `', array_map(
                    fn ($i) => $statement->getColumnMeta($i)['name'],
                    range(0, count($row) - 1),
                )).'`';
            }

            $batch[] = '('.implode(', ', array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), $row)).')';
            $count++;

            if (count($batch) === self::ROWS_PER_INSERT) {
                gzwrite($gz, "INSERT INTO `{$table}` ({$columns}) VALUES ".implode(', ', $batch).";\n");
                $batch = [];
            }
        }

        if ($batch) {
            gzwrite($gz, "INSERT INTO `{$table}` ({$columns}) VALUES ".implode(', ', $batch).";\n");
        }

        $statement->closeCursor();
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        gzwrite($gz, "\n");

        return $count;
    }
}
