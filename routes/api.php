<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Do not expose profile attributes beyond what the API client needs.
Route::get('/user', function (Request $request) {
    return response()->json([
        'data' => $request->user()->only(['id', 'name', 'email', 'role']),
    ]);
})->middleware(['auth:sanctum', 'throttle:api']);
