<?php

use Illuminate\Support\Facades\Schedule;

// Run by Windows Task Scheduler every minute: php artisan schedule:run (see deploy\register-scheduler-task.ps1).

// Feed sources without a fetch interval: once a day.
Schedule::command('news:fetch')->dailyAt('07:00')->withoutOverlapping();

// Feed sources with an interval (e.g. hot deals every hour): checked hourly, each runs when its interval has passed.
Schedule::command('news:fetch --frequent')->hourlyAt(5)->withoutOverlapping();

Schedule::command('posts:prune-images')->dailyAt('04:15');
