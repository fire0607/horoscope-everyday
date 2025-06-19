<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/horoscope/fetch', function () {
    Artisan::call('app:fetch-horoscope-command');
    return response()->json(['message' => 'Horoscope fetching initiated. Check logs for details.']);
});

Route::get('/horoscopes', [App\Http\Controllers\HoroscopeController::class, 'index']);