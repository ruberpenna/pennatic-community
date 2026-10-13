<?php

use App\Models\Attendance;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component {
    #[Url(except: '')]
    public string $month = '';

    public function rendering(View $view): void
    {
        $view->title(__('Attendance'));
    }

    public function check(): void
    {
        try {
            auth()->user()->attendances()->firstOrCreate(['attended_on' => today()->toDateString()]);
        } catch (UniqueConstraintViolationException) {
            // Double click: already checked in today.
        }

        $this->month = '';
    }

    public function with(): array
    {
        $user = auth()->user();
        $start = $this->monthStart();

        $attended = $user->attendances()
            ->whereBetween('attended_on', [$start->toDateString(), $start->endOfMonth()->toDateString()])
            ->pluck('attended_on')
            ->map(fn ($date) => $date->toDateString())
            ->flip();

        return [
            'start' => $start,
            'weeks' => $this->calendarWeeks($start),
            'attended' => $attended,
            'checkedToday' => $user->attendances()->whereDate('attended_on', today())->exists(),
            'total' => $user->attendances()->count(),
            'streak' => $this->streak(),
            'todayCount' => Attendance::whereDate('attended_on', today())->count(),
        ];
    }

    private function monthStart(): CarbonImmutable
    {
        $current = CarbonImmutable::today()->startOfMonth();

        if (! preg_match('/^\d{4}-\d{2}$/', $this->month)) {
            return $current;
        }

        $month = CarbonImmutable::createFromFormat('!Y-m', $this->month);

        // No future months and nothing older than 10 years.
        return $month->between($current->subYears(10), $current) ? $month : $current;
    }

    /**
     * @return Collection<int, Collection<int, ?CarbonImmutable>> weeks starting on Sunday, null for padding
     */
    private function calendarWeeks(CarbonImmutable $start): Collection
    {
        $days = collect(array_fill(0, $start->dayOfWeek, null));

        for ($day = $start; $day->month === $start->month; $day = $day->addDay()) {
            $days->push($day);
        }

        return $days->pad((int) ceil($days->count() / 7) * 7, null)->chunk(7)->map->values();
    }

    /**
     * Consecutive days up to today (or yesterday, if not checked in yet today).
     */
    private function streak(): int
    {
        $dates = auth()->user()->attendances()
            ->where('attended_on', '>=', today()->subDays(366)->toDateString())
            ->orderByDesc('attended_on')
            ->pluck('attended_on')
            ->map(fn ($date) => $date->toDateString())
            ->flip();

        $day = $dates->has(today()->toDateString()) ? today() : today()->subDay();
        $streak = 0;

        while ($dates->has($day->toDateString())) {
            $streak++;
            $day = $day->subDay();
        }

        return $streak;
    }
}; ?>

<div class="flex max-w-3xl flex-col gap-6">
    <flux:heading size="xl" level="1">{{ __('Attendance') }}</flux:heading>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:subheading>{{ __('Current streak') }}</flux:subheading>
            <p class="text-2xl font-semibold">{{ trans_choice(':count day|:count days', $streak) }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:subheading>{{ __('Total check-ins') }}</flux:subheading>
            <p class="text-2xl font-semibold">{{ trans_choice(':count day|:count days', $total) }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:subheading>{{ __('Checked in today') }}</flux:subheading>
            <p class="text-2xl font-semibold">{{ trans_choice(':count member|:count members', $todayCount) }}</p>
        </div>
    </div>

    @if ($checkedToday)
        <flux:callout variant="success" icon="check-circle" :heading="__('You have checked in today.')" />
    @else
        <flux:button variant="primary" icon="calendar-days" wire:click="check" class="self-start">{{ __('Check in') }}</flux:button>
    @endif

    <section class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <flux:button size="sm" variant="ghost" icon="chevron-left" wire:click="$set('month', '{{ $start->subMonth()->format('Y-m') }}')" :aria-label="__('Previous month')" />
            <flux:heading>{{ $start->format('Y.m') }}</flux:heading>
            <flux:button size="sm" variant="ghost" icon="chevron-right" wire:click="$set('month', '{{ $start->addMonth()->format('Y-m') }}')" :disabled="$start->isCurrentMonth()" :aria-label="__('Next month')" />
        </div>

        <table class="w-full table-fixed text-center text-sm">
            <thead class="text-zinc-500">
                <tr>
                    @foreach ([__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')] as $weekday)
                        <th class="py-2 font-normal">{{ $weekday }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($weeks as $week)
                    <tr>
                        @foreach ($week as $day)
                            <td class="p-1">
                                @if ($day)
                                    <span @class([
                                        'mx-auto flex size-9 items-center justify-center rounded-full',
                                        'bg-accent text-accent-foreground font-semibold' => $attended->has($day->toDateString()),
                                        'ring-2 ring-zinc-400' => $day->isToday(),
                                        'text-zinc-400 dark:text-zinc-600' => $day->isFuture(),
                                    ])>{{ $day->day }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
