<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\API\SocialLoginController;
use App\Http\Controllers\API\TipsCareApiController;
use App\Http\Controllers\Web\Backend\AdminController;
use Laravel\Cashier\Http\Controllers\WebHookController;
use App\Http\Controllers\Web\Backend\TipsCareController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'user'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::post('stripe/webhook', [WebHookController::class, 'handleWebhook']);


/*Route::get('social-login/{provider}', [SocialLoginController::class, 'RedirectToProvider'])->name('social.login');
Route::get('social-login/callback/{provider}', [SocialLoginController::class, 'HandleProviderCallback']);*/

require __DIR__.'/auth.php';
require __DIR__.'/backend.php';
