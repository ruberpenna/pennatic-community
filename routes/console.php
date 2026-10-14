<?php

use Illuminate\Support\Facades\Schedule;

// Run by Windows Task Scheduler every minute: php artisan schedule:run (see deploy\register-scheduler-task.ps1).

Schedule::command('news:fetch')->dailyAt('07:00')->withoutOverlapping();

Schedule::command('posts:prune-images')->dailyAt('04:15');
