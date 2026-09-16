<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\OpmlController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
|
| Accounts are created by an administrator, so the only public route is the
| sign in form. Everything else requires a session.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Main page
    Route::get('/', [MainController::class, 'index'])->name('main');

    // Feeds
    Route::get('/feeds/add', [FeedController::class, 'add'])->name('feed.add');
    Route::post('/feeds', [FeedController::class, 'store'])->name('feed.store');
    Route::get('/feeds/{feed}', [FeedController::class, 'show'])->name('feed.show');
    Route::get('/feeds/{feed}/edit', [FeedController::class, 'edit'])->name('feed.edit');
    Route::patch('/feeds/{feed}', [FeedController::class, 'update'])->name('feed.update');
    Route::delete('/feeds/{feed}', [FeedController::class, 'delete'])->name('feed.delete');
    Route::post('/feeds/{feed}/refresh', [FeedController::class, 'refresh'])
        ->name('feed.refresh')->middleware('throttle:10,1');
    Route::post('/feeds/{feed}/read', [FeedController::class, 'markAllRead'])->name('feed.read-all');

    // Articles
    Route::get('/items/{item}/open', [ItemController::class, 'open'])->name('item.open');
    Route::post('/items/{item}/read', [ItemController::class, 'read'])->name('item.read');
    Route::post('/items/{item}/unread', [ItemController::class, 'unread'])->name('item.unread');

    // OPML import / export
    Route::get('/opml', [OpmlController::class, 'index'])->name('opml.index');
    Route::get('/opml/export', [OpmlController::class, 'export'])->name('opml.export');
    Route::post('/opml/import', [OpmlController::class, 'import'])
        ->name('opml.import')->middleware('throttle:5,1');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.show');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Account management (administrators only)
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
