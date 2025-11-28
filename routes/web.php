<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'App\Http\Controllers\MainController@index')->name('main');

Route::get('/feeds/add', 'App\Http\Controllers\FeedController@add')->name('feed.add');
Route::post('/feeds', 'App\Http\Controllers\FeedController@store')->name('feed.store');
Route::get('/feeds/{feed}', 'App\Http\Controllers\FeedController@show')->name('feed.show');
Route::get('/feeds/{feed}/edit', 'App\Http\Controllers\FeedController@edit')->name('feed.edit');
Route::patch('/feeds/{feed}', 'App\Http\Controllers\FeedController@update')->name('feed.update');
Route::delete('/feeds/{feed}', 'App\Http\Controllers\FeedController@delete')->name('feed.delete');

Route::get('/settings', 'App\Http\Controllers\SettingsController@index')->name('settings.show');
Route::patch('/settings/', 'App\Http\Controllers\SettingsController@update')->name('settings.update');

Route::get('/iframe', 'App\Http\Controllers\IframeController@proxy')->name('iframe.proxy');