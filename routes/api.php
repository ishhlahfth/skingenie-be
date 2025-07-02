<?php

use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\RefOptionController;
use App\Http\Controllers\SociollaController;
use Illuminate\Support\Facades\Route;

Route::prefix('options')->group(function () {
    Route::get('/categories', [RefOptionController::class, 'getCategoryOption']);
    Route::get('/skin-type', [RefOptionController::class, 'getSkinTypeOption']);
    Route::get('/concern', [RefOptionController::class, 'getSkinConcernOption']);
});
Route::prefix('submission')->group(function () {
    Route::post('request', [RecommendationController::class, 'submitRecommendation']);
});

