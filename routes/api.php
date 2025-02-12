<?php


use Illuminate\Support\Facades\Route;


Route::get('/test', [\App\Http\Controllers\API\TestContrtoller::class, 'index']);
