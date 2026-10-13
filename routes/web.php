<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'home')->name('home');

// The starter kit sends users to "dashboard" after login; the home page is the community front page.
Route::redirect('dashboard', '/')->name('dashboard');

Volt::route('boards/{board}', 'boards.show')->name('boards.show');
Volt::route('posts/{post}', 'posts.show')->whereNumber('post')->name('posts.show');
Volt::route('notices', 'notices.index')->name('notices.index');
Volt::route('notices/{notice}', 'notices.show')->whereNumber('notice')->name('notices.show');

Route::middleware(['auth'])->group(function () {
    Volt::route('boards/{board}/write', 'posts.create')->name('posts.create');
    Volt::route('posts/{post}/edit', 'posts.edit')->whereNumber('post')->name('posts.edit');
    Route::get('attachments/{attachment}', AttachmentController::class)->name('attachments.download');
    Volt::route('my/activity', 'my.activity')->name('my.activity');

    Volt::route('messages', 'messages.index')->name('messages.index');
    Volt::route('messages/new', 'messages.create')->name('messages.create');
    Volt::route('messages/{message}', 'messages.show')->whereNumber('message')->name('messages.show');
    Volt::route('memos', 'memos.index')->name('memos.index');
    Volt::route('attendance', 'attendance')->name('attendance');
    Volt::route('contact', 'contact')->name('contact');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
