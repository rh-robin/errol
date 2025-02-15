<?php


use App\Http\Controllers\API\PetApiController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\TipsCareApiController;
use Illuminate\Support\Facades\Route;


Route::get('/test', [\App\Http\Controllers\API\TestContrtoller::class, 'index']);


Route::post('/socialLogin', [SocialLoginController::class, 'SocialLogin']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [SocialLoginController::class, 'logout']);
});

Route::post('/pet/store', [PetApiController::class, 'store']);
Route::post('/pet/update/{id}', [PetApiController::class, 'update']);



Route::get('/tips-and-care', [TipsCareApiController::class, 'index']);
