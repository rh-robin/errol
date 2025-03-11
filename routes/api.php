<?php

use App\Http\Controllers\API\BreedsApiController;
use App\Http\Controllers\API\FoodApiController;
use App\Http\Controllers\API\PetApiController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\TipsCareApiController;
use App\Http\Controllers\API\WeightApiController;
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

/*================= food api ==================*/
Route::post('/analyze-food', [FoodApiController::class, 'analyzeFood']);
Route::post('/food-info/date', [FoodApiController::class, 'getFoodInfoByDate']);
Route::post('/analyze-food/claude', [FoodApiController::class, 'analyzeFoodClaude']);

/* ====================== weight api ===================*/
Route::post('/weight/store', [WeightApiController::class, 'storeWeight']);
Route::get('/weight/{pet_id}', [WeightApiController::class, 'getWeight']);

/* ======================== stripe =================*/
/*Route::post('/create-customer', [StripeController::class, 'createCustomer']);
Route::post('/subscribe', [StripeController::class, 'subscribe']);
Route::post('/payment-intent', [StripeController::class, 'createPaymentIntent']);
Route::post('/webhook', [StripeController::class, 'handleWebhook']);*/

Route::get('/tips-and-care', [TipsCareApiController::class, 'index']);
Route::get('/breeds', [BreedsApiController::class, 'index']);
Route::get('/fetch-breeds/cat', [BreedsApiController::class, 'catBreeds']);
Route::get('/fetch-breeds/dog', [BreedsApiController::class, 'dogBreeds']);

Route::get('/terms-conditions', [TipsCareApiController::class, 'terms']);
Route::get('/privacy-policy', [TipsCareApiController::class, 'policy']);

/*================= Subscriptions APIS ==================*/
Route::middleware(['auth:api'])->group(function () {
    Route::post('create-subscription', [SubscriptionController::class, 'createSubscription']);
    Route::post('cancel-subscription', [SubscriptionController::class, 'cancelSubscription']);
});
// Subscription plans
Route::get('subscription-plans', [SubscriptionController::class, 'getPlans']);

Route::any('checkout/success', [SubscriptionController::class, 'checkoutSuccess'])->name('checkout.success');
Route::get('checkout/cancel', [SubscriptionController::class, 'checkoutCancel'])->name('checkout.cancel');
