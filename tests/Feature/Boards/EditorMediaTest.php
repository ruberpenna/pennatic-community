<?php

namespace Tests\Feature\Boards;

use App\Models\Board;
use App\Models\Post;
use App\Models\User;
use App\Support\PostContent;
use App\Support\VideoEmbed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class EditorMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_youtube_and_chzzk_links_become_players(): void
    {
        $cases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://youtube.com/watch?feature=share&amp;v=dQw4w9WgXcQ' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ?si=abc' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://chzzk.naver.com/video/1234567' => 'https://chzzk.naver.com/embed/video/1234567',
            'https://chzzk.naver.com/clips/Zd9JbkRMzW' => 'https://chzzk.naver.com/embed/clip/Zd9JbkRMzW',
        ];

        foreach ($cases as $url => $embed) {
            $this->assertStringContainsString('<iframe src="'.$embed.'"', VideoEmbed::render("<div>{$url}</div>"), $url);
            $this->assertStringContainsString('<iframe src="'.$embed.'"', VideoEmbed::render('<div><a href="'.$url.'">링크</a></div>'), $url);
        }
    }

    public function test_other_links_and_attributes_are_left_alone(): void
    {
        $html = '<div><a href="https://example.com/watch?v=dQw4w9WgXcQ">x</a> https://vimeo.com/123 '
            .'<img src="/storage/a.png" alt="https://youtu.be/dQw4w9WgXcQ"></div>';

        $this->assertSame($html, VideoEmbed::render($html));
    }

    public function test_a_crafted_link_cannot_inject_markup(): void
    {
        $html = VideoEmbed::render('<div>https://youtu.be/dQw4w9WgXcQ"onload="alert(1)</div>');

        // The ID is matched strictly, so the rest of the text stays outside the player tag as plain text.
        preg_match('~<iframe[^>]*>~', $html, $tag);
        $this->assertStringContainsString('src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"', $tag[0]);
        $this->assertStringNotContainsString('onload', $tag[0]);
        $this->assertSame(1, substr_count($html, '<iframe'));
    }

    public function test_stored_content_keeps_local_images_but_not_external_ones_or_iframes(): void
    {
        $clean = PostContent::clean(
            '<div><img src="/storage/post-images/a.png" width="10" height="10">'
            .'<img src="https://evil.example/pixel.gif">'
            .'<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe></div>'
        );

        $this->assertStringContainsString('/storage/post-images/a.png', $clean);
        $this->assertStringNotContainsString('evil.example', $clean);
        $this->assertStringNotContainsString('iframe', $clean);
    }

    public function test_editor_image_upload_stores_a_reencoded_image(): void
    {
        Storage::fake('public');
        $board = Board::factory()->create();
        $this->actingAs(User::factory()->create());

        $component = Volt::test('posts.create', ['board' => $board])
            ->set('editorImage', UploadedFile::fake()->image('photo.png', 2400, 1200))
            ->call('storeEditorImage')
            ->assertHasNoErrors();

        $url = $component->effects['returns'][0];
        $this->assertMatchesRegularExpression('~^/storage/post-images/\d{4}/\d{2}/[0-9a-f-]{36}\.png$~', $url);

        $path = substr($url, strlen('/storage/'));
        Storage::disk('public')->assertExists($path);
        [$width] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame(1600, $width);
    }

    public function test_editor_rejects_non_images_and_large_files(): void
    {
        Storage::fake('public');
        $board = Board::factory()->create();
        $this->actingAs(User::factory()->create());

        Volt::test('posts.create', ['board' => $board])
            ->set('editorImage', UploadedFile::fake()->create('shell.php.png', 10, 'image/png'))
            ->call('storeEditorImage')
            ->assertHasErrors('editorImage');

        Volt::test('posts.create', ['board' => $board])
            ->set('editorImage', UploadedFile::fake()->image('big.jpg')->size(6000))
            ->call('storeEditorImage')
            ->assertHasErrors('editorImage');

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_prune_removes_only_old_unused_images(): void
    {
        Storage::fake('public');
        $disk = Storage::disk('public');

        foreach (['used', 'orphan', 'fresh', 'trashed'] as $name) {
            $disk->put("post-images/2026/10/{$name}.png", 'x');
        }
        $old = now()->subDays(2)->getTimestamp();
        foreach (['used', 'orphan', 'trashed'] as $name) {
            touch($disk->path("post-images/2026/10/{$name}.png"), $old);
        }

        Post::factory()->create(['content' => '<div><img src="/storage/post-images/2026/10/used.png"></div>']);
        Post::factory()->create(['content' => '<div><img src="/storage/post-images/2026/10/trashed.png"></div>'])->delete();

        $this->artisan('posts:prune-images')->assertSuccessful();

        $disk->assertExists('post-images/2026/10/used.png');
        $disk->assertExists('post-images/2026/10/trashed.png');
        $disk->assertExists('post-images/2026/10/fresh.png');
        $disk->assertMissing('post-images/2026/10/orphan.png');
    }

    public function test_video_link_in_a_post_is_shown_as_a_player(): void
    {
        $post = Post::factory()->create(['content' => '<div>https://youtu.be/dQw4w9WgXcQ</div>']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    }
}
