<?php
use App\Http\Controllers\Web\Backend\AdminController;
use App\Http\Controllers\Web\Backend\BreedController;
use App\Http\Controllers\Web\Backend\TipsCareController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        /*============ tips and care routes ==========*/
        Route::prefix('tips-and-care')
            ->name('tips_care.')
            ->controller(TipsCareController::class)
            ->group(function () {
                Route::get('/index', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/destroy/{id}', 'destroy')->name('destroy');
        });

        /*============ tips and care routes ==========*/
        Route::prefix('breed')
            ->name('breed.')
            ->controller(BreedController::class)
            ->group(function () {
                Route::get('/index', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/destroy/{id}', 'destroy')->name('destroy');
            });
});

