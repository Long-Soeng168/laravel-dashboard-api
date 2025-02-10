<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function (Request $request) {
    return 'hello World';
});

Route::apiResource('categories', CategoryController::class);
Route::post('categories/{id}/update', [CategoryController::class, 'update']);

Route::apiResource('products', ProductController::class);
Route::post('products/{id}/update', [ProductController::class, 'update']);
