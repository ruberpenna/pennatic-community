<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Restores a file written by db:backup-sql. Replaces every table that is in the backup.
 */
class RestoreDatabase extends Command
{
    protected $signature = 'db:restore-sql {path : Backup file (.sql.gz)} {--connection= : Database connection, default connection if omitted} {--force : Do not ask for confirmation}';

    protected $description = 'Restore a gzip SQL backup made by db:backup-sql';

    public function handle(): int
    {
        $connection = DB::connection($this->option('connection') ?: null);
        $database = $connection->getDatabaseName();

        if (! $this->option('force') && ! $this->confirm("Replace the tables in database `{$database}` with this backup?")) {
            return self::FAILURE;
        }

        $gz = @gzopen($this->argument('path'), 'rb');

        if ($gz === false) {
            $this->error('Cannot open '.$this->argument('path'));

            return self::FAILURE;
        }

        $pdo = $connection->getPdo();
        $buffer = '';
        $statements = 0;

        // Statements end with ";" at the end of a line (see db:backup-sql); CREATE TABLE spans several lines.
        while (($line = gzgets($gz)) !== false) {
            if ($buffer === '' && (str_starts_with($line, '--') || trim($line) === '')) {
                continue;
            }

            $buffer .= $line;

            if (str_ends_with(rtrim($line), ';')) {
                $pdo->exec($buffer);
                $buffer = '';
                $statements++;
            }
        }

        gzclose($gz);

        $this->info("Restored {$statements} statements into `{$database}`.");

        return self::SUCCESS;
    }
}
