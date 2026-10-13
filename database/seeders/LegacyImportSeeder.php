<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\BoardFolder;
use App\Models\Notice;
use App\Models\User;
use App\Support\PostContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Imports data from the pre-Laravel "legacy" connection.
 *
 * As of 2026-10-13 the legacy DB holds only the admin account, one category
 * row and six notices; all other tables are empty.
 */
class LegacyImportSeeder extends Seeder
{
    public function run(): void
    {
        $legacy = DB::connection('legacy');

        DB::transaction(function () use ($legacy) {
            $users = $this->importUsers($legacy);
            $this->importCategories($legacy);
            $this->importNotices($legacy, $users);
        });
    }

    /**
     * @return array<string, int> legacy mem_id => new user id
     */
    private function importUsers($legacy): array
    {
        $map = [];

        foreach ($legacy->table('tbl_member')->orderBy('mem_idx')->get() as $row) {
            $user = User::withTrashed()->firstOrNew(['username' => strtolower($row->mem_id)]);

            $user->forceFill([
                'name' => $row->mem_nick,
                'email' => strtolower($row->mem_email),
                'email_verified_at' => $row->mem_join,
                // Legacy passwords were stored in plain text and have been exposed.
                'password' => Hash::make($row->mem_pwd),
                'password_must_change' => true,
                'role' => (int) $row->mem_status === 2 ? 'admin' : 'member',
                'level' => 1,
                'intro' => $row->mem_intro ?: null,
                'created_at' => $row->mem_join,
                'deleted_at' => (int) $row->mem_status === 0 ? ($row->mem_withdraw ?? now()) : null,
            ])->save();

            $map[$row->mem_id] = $user->id;
        }

        return $map;
    }

    /**
     * Legacy categories are tbl_board rows with board_status = 3.
     */
    private function importCategories($legacy): void
    {
        $rows = $legacy->table('tbl_board')->where('board_status', 3)->orderBy('board_idx')->get();

        foreach ($rows as $row) {
            $folder = BoardFolder::firstOrCreate(['name' => $row->board_folder]);

            if ($row->board_group === '') {
                continue;
            }

            $board = Board::firstOrCreate([
                'board_folder_id' => $folder->id,
                'name' => $row->board_group,
            ]);

            if ($row->board_subject !== '') {
                $board->update([
                    'subjects' => array_values(array_unique([...($board->subjects ?? []), $row->board_subject])),
                ]);
            }
        }
    }

    /**
     * @param  array<string, int>  $users
     */
    private function importNotices($legacy, array $users): void
    {
        $adminId = User::where('role', 'admin')->value('id');

        foreach ($legacy->table('tbl_main')->orderBy('main_idx')->get() as $row) {
            $notice = Notice::withTrashed()->firstOrNew([
                'title' => $row->main_title,
                'created_at' => $row->main_date,
            ]);

            $notice->forceFill([
                'user_id' => $adminId,
                'content' => PostContent::clean($this->extractInlineImages($row->main_content)),
                'updated_at' => $row->main_date,
                'deleted_at' => (int) $row->main_status === 1 ? null : now(),
            ])->save();
        }
    }

    /**
     * The legacy editor embedded images as base64 data URIs, which the
     * purifier strips. Store them on the public disk and link to the file.
     */
    private function extractInlineImages(string $html): string
    {
        return preg_replace_callback(
            '#src="data:image/(png|jpe?g|gif|webp);base64,([A-Za-z0-9+/=]+)"#',
            function (array $m) {
                $bytes = base64_decode($m[2], true);

                if ($bytes === false) {
                    return 'src=""';
                }

                $path = 'notices/'.hash('sha256', $bytes).'.'.($m[1] === 'jpeg' ? 'jpg' : $m[1]);
                Storage::disk('public')->put($path, $bytes);

                return 'src="/storage/'.$path.'"';
            },
            $html,
        );
    }
}
