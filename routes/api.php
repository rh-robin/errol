<?php


use App\Http\Controllers\API\SocialLoginController;
use Illuminate\Support\Facades\Route;


Route::get('/test', [\App\Http\Controllers\API\TestContrtoller::class, 'index']);


Route::post('/socialLogin', [SocialLoginController::class, 'SocialLogin']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [SocialLoginController::class, 'logout']);
});

