<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationViewTest extends TestCase
{
    private function render(int $page): string
    {
        $paginator = new LengthAwarePaginator(range(1, 20), 544, 20, $page);

        return $paginator->links('livewire::tailwind')->toHtml();
    }

    /**
     * @return list<int>
     */
    private function pages(string $html): array
    {
        preg_match_all('/gotoPage\((\d+),|aria-current="page"[^>]*>(\d+)</', $html, $matches);

        return array_map('intval', array_filter(array_merge($matches[1], $matches[2])));
    }

    public function test_only_one_page_on_each_side_of_the_current_one_is_linked(): void
    {
        $pages = $this->pages($this->render(6));
        sort($pages);

        $this->assertSame([1, 2, 5, 6, 7, 27, 28], $pages);
    }

    public function test_summary_is_shown_in_korean_on_its_own_line(): void
    {
        app()->setLocale('ko');

        $this->assertStringContainsString('전체 544개 · 101–120', $this->render(6));
    }
}
