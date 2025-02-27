<?php


use App\Http\Controllers\API\BreedsApiController;
use App\Http\Controllers\API\FoodApiController;
use App\Http\Controllers\API\PetApiController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\TipsCareApiController;
use App\Http\Controllers\Web\Backend\BreedController;
use Illuminate\Support\Facades\Route;





Route::post('/socialLogin', [SocialLoginController::class, 'SocialLogin']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [SocialLoginController::class, 'logout']);
    Route::get('/profile', [SocialLoginController::class, 'getProfile']);

    /* ==========  pet api ==========*/
    Route::post('/pet/store', [PetApiController::class, 'store']);
    Route::post('/pet/update/{id}', [PetApiController::class, 'update']);
    Route::get('/my-pet', [PetApiController::class, 'myPet']);
});

Route::post('/analyze-food', [FoodApiController::class, 'analyzeFood']);
Route::post('/food-info/date', [FoodApiController::class, 'getFoodInfoByDate']);





Route::get('/tips-and-care', [TipsCareApiController::class, 'index']);
Route::get('/breeds', [BreedsApiController::class, 'index']);
Route::get('/fetch-breeds/cat', [BreedsApiController::class, 'catBreeds']);
Route::get('/fetch-breeds/dog', [BreedsApiController::class, 'dogBreeds']);


Route::get('/terms-conditions', [TipsCareApiController::class, 'terms']);
Route::get('/privacy-policy', [TipsCareApiController::class, 'policy']);
