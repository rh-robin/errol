<?php


use App\Http\Controllers\API\BreedsApiController;
use App\Http\Controllers\API\FoodApiController;
use App\Http\Controllers\API\PetApiController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\TipsCareApiController;
use App\Http\Controllers\Web\Backend\BreedController;
use Illuminate\Support\Facades\Route;


Route::get('/test', [\App\Http\Controllers\API\TestContrtoller::class, 'index']);


Route::post('/socialLogin', [SocialLoginController::class, 'SocialLogin']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [SocialLoginController::class, 'logout']);
    Route::post('/pet/store', [PetApiController::class, 'store']);
    Route::post('/pet/update/{id}', [PetApiController::class, 'update']);
});

Route::post('analyze-food', [FoodApiController::class, 'analyzeFood']);





Route::get('/tips-and-care', [TipsCareApiController::class, 'index']);
Route::get('/breeds', [BreedsApiController::class, 'index']);


Route::get('/terms-conditions', [TipsCareApiController::class, 'terms']);
Route::get('/privacy-policy', [TipsCareApiController::class, 'policy']);
