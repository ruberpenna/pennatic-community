<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\BoardFolder;
use App\Models\Comment;
use App\Models\Notice;
use App\Models\Post;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with local development data.
     *
     * Production data comes from LegacyImportSeeder instead.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'admin',
            'name' => '운영자',
            'email' => 'admin@example.com',
        ]);

        $members = User::factory(5)->create();

        $folder = BoardFolder::factory()->create(['name' => '커뮤니티']);
        $boards = collect(['자유게시판', '질문답변'])->map(fn (string $name, int $i) => Board::factory()->create([
            'board_folder_id' => $folder->id,
            'name' => $name,
            'subjects' => ['일반', '정보'],
            'sort_order' => $i,
        ]));

        foreach ($boards as $board) {
            Post::factory(15)
                ->recycle($members)
                ->has(Comment::factory(3)->recycle($members))
                ->create(['board_id' => $board->id]);
        }

        Notice::factory(3)->create(['user_id' => $admin->id]);
    }
}
