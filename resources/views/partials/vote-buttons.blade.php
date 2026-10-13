{{--
    Up/down vote buttons. Expects:
    $up, $down (counts), $mine (1, -1 or 0), $action (sprintf pattern for wire:click with %d for the value),
    $canVote (false hides the click handlers, e.g. on your own post), $size (Flux button size).
--}}
<flux:button
    :size="$size"
    :variant="$mine === 1 ? 'primary' : 'ghost'"
    icon="hand-thumb-up"
    :disabled="! $canVote"
    :wire:click="$canVote ? sprintf($action, 1) : null"
    :aria-label="__('Upvote')"
>{{ $up }}</flux:button>
<flux:button
    :size="$size"
    :variant="$mine === -1 ? 'primary' : 'ghost'"
    icon="hand-thumb-down"
    :disabled="! $canVote"
    :wire:click="$canVote ? sprintf($action, -1) : null"
    :aria-label="__('Downvote')"
>{{ $down }}</flux:button>
