<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Comment;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommunityStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = today();

        return [
            Stat::make(__('Members'), number_format(User::count()))
                ->description(__(':count joined today', ['count' => User::whereDate('created_at', $today)->count()])),
            Stat::make(__('Posts'), number_format(Post::count()))
                ->description(__(':count written today', ['count' => Post::whereDate('created_at', $today)->count()])),
            Stat::make(__('Comments'), number_format(Comment::count()))
                ->description(__(':count written today', ['count' => Comment::whereDate('created_at', $today)->count()])),
            Stat::make(__('Checked in today'), number_format(Attendance::whereDate('attended_on', $today)->count())),
            Stat::make(__('Unread messages'), number_format(Message::inboxOf(auth()->user())->whereNull('read_at')->count()))
                ->description(__('Including inquiries'))
                ->url(route('messages.index')),
        ];
    }
}
