<?php

namespace Tests\Feature\Boards;

use App\Console\Commands\FetchNews;
use App\Models\FeedSource;
use App\Models\Post;
use App\Models\User;
use App\Support\FeedReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsFeedTest extends TestCase
{
    use RefreshDatabase;

    private const RSS = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0"><channel><title>Headlines</title>
          <item>
            <title><![CDATA["첫 번째" 기사 <b>제목</b>]]></title>
            <link>https://news.example.test/a?id=1&amp;from=rss</link>
            <guid>https://news.example.test/a?id=1</guid>
            <description><![CDATA[<p>요약 <script>alert(1)</script>입니다.</p>]]></description>
            <pubDate>Wed, 14 Oct 2026 20:03:00 +0900</pubDate>
          </item>
          <item>
            <title>두 번째 기사</title>
            <link>javascript:alert(1)</link>
            <guid>bad-link</guid>
          </item>
          <item>
            <title>세 번째 기사</title>
            <link>https://news.example.test/c</link>
            <description>짧은 요약</description>
          </item>
        </channel></rss>
        XML;

    public function test_rss_and_atom_are_parsed(): void
    {
        $items = FeedReader::parse(self::RSS);

        $this->assertCount(2, $items); // the javascript: link is dropped
        $this->assertSame('"첫 번째" 기사 제목', $items[0]['title']);
        $this->assertSame('https://news.example.test/a?id=1&from=rss', $items[0]['link']);
        $this->assertSame('https://news.example.test/c', $items[1]['guid']);

        $atom = '<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"><entry><id>tag:x,1</id>'
            .'<title>Atom 글</title><link rel="alternate" href="https://blog.example.test/1"/><summary>요약</summary>'
            .'<updated>2026-10-14T10:00:00Z</updated></entry></feed>';

        $this->assertSame('https://blog.example.test/1', FeedReader::parse($atom)[0]['link']);
    }

    public function test_new_items_are_posted_once_by_the_news_bot(): void
    {
        Http::fake(['news.example.test/*' => Http::response(self::RSS)]);
        $source = FeedSource::factory()->create(['url' => 'https://news.example.test/rss.xml', 'name' => '테스트 뉴스']);

        $this->artisan('news:fetch')->assertSuccessful();
        $this->artisan('news:fetch')->assertSuccessful();

        $posts = Post::where('board_id', $source->board_id)->orderBy('id')->get();
        $this->assertCount(2, $posts);
        $this->assertSame(FetchNews::BOT_USERNAME, $posts[0]->user->username);
        $this->assertTrue($posts[0]->user->hasVerifiedEmail());

        // Newer feed position gets the higher id; first feed item is the last posted.
        $first = $posts[1];
        $this->assertSame('"첫 번째" 기사 제목', $first->title);
        $this->assertStringContainsString('요약 입니다.', $first->content);
        $this->assertStringNotContainsString('script', $first->content);
        $this->assertStringContainsString('href="https://news.example.test/a?id=1&amp;from=rss"', $first->content);
        $this->assertStringContainsString('원문 보기 · 테스트 뉴스', $first->content);

        $this->assertNotNull($source->refresh()->last_fetched_at);
        $this->assertNull($source->last_error);
    }

    public function test_inactive_sources_are_skipped_and_errors_are_recorded(): void
    {
        Http::fake([
            'down.example.test/*' => Http::response('', 500),
            '*' => Http::response(self::RSS),
        ]);

        FeedSource::factory()->create(['is_active' => false]);
        $broken = FeedSource::factory()->create(['url' => 'https://down.example.test/rss']);

        $this->artisan('news:fetch')->assertFailed();

        $this->assertSame(0, Post::count());
        $this->assertNotNull($broken->refresh()->last_error);
    }

    public function test_the_bot_cannot_log_in_with_a_guessable_password(): void
    {
        $bot = FetchNews::bot();

        $this->assertFalse(auth()->attempt(['username' => FetchNews::BOT_USERNAME, 'password' => 'password']));
        $this->assertSame($bot->id, FetchNews::bot()->id);
        $this->assertSame(1, User::where('username', FetchNews::BOT_USERNAME)->count());
    }

    public function test_admin_can_manage_sources(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $source = FeedSource::factory()->create();

        $this->get('/admin/feed-sources')->assertOk()->assertSee($source->name);
        $this->get("/admin/feed-sources/{$source->id}/edit")->assertOk();
        $this->get('/admin/feed-sources/create')->assertOk();
    }
}
