@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';

// One page on each side of the current one keeps the row narrow enough for phones and tablets,
// e.g. "1 2 … 5 6 7 … 27 28" instead of the default ten-plus links.
$window = \Illuminate\Pagination\UrlWindow::make($paginator->onEachSide(1));
$elements = array_filter([
    $window['first'],
    is_array($window['slider']) ? '...' : null,
    $window['slider'],
    is_array($window['last']) ? '...' : null,
    $window['last'],
]);

$pageName = $paginator->getPageName();
$duskSuffix = $pageName == 'page' ? '' : '.'.$pageName;
$base = 'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm';
$link = $base.' text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700';
$disabled = $base.' cursor-default text-zinc-300 dark:text-zinc-600';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col items-center gap-2">
            <div class="flex flex-wrap items-center justify-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="{{ $disabled }}" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <flux:icon.chevron-left variant="micro" />
                    </span>
                @else
                    <button type="button" wire:click="previousPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="previousPage{{ $duskSuffix }}" class="{{ $link }}" aria-label="{{ __('pagination.previous') }}">
                        <flux:icon.chevron-left variant="micro" />
                    </button>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="{{ $disabled }}" aria-disabled="true">{{ $element }}</span>
                    @else
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $pageName }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="{{ $base }} bg-zinc-800 font-medium text-white dark:bg-white dark:text-zinc-800">{{ $page }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="{{ $link }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="nextPage{{ $duskSuffix }}" class="{{ $link }}" aria-label="{{ __('pagination.next') }}">
                        <flux:icon.chevron-right variant="micro" />
                    </button>
                @else
                    <span class="{{ $disabled }}" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <flux:icon.chevron-right variant="micro" />
                    </span>
                @endif
            </div>

            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ __(':total items · :first–:last', ['total' => number_format($paginator->total()), 'first' => $paginator->firstItem(), 'last' => $paginator->lastItem()]) }}
            </p>
        </nav>
    @endif
</div>
