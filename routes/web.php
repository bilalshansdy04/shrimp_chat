<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/test-ws', 'test-ws');
Route::view('/test-chat', 'test-chat');
