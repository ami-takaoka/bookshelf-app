<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ログイン（トークン発行）
    Route::post('/login', [AuthController::class, 'login']);

    // 読み取り系API（認証不要）
    Route::apiResource('books', BookController::class)
        ->only(['index', 'show']);

    // 書き込み系API（Sanctum認証）
    Route::middleware('auth:sanctum')->group(function () {

        Route::apiResource('books', BookController::class)
            ->only(['store', 'update', 'destroy']);
    });
});
