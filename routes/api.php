<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('admin')->group(function () {
    Route::post('login', [App\Http\Controllers\Admin\AdminAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('sellers', [App\Http\Controllers\Admin\SellerManagementController::class, 'store']);
        Route::get('sellers', [App\Http\Controllers\Admin\SellerManagementController::class, 'index']);
    });
});

Route::prefix('seller')->group(function () {
    Route::post('login', [App\Http\Controllers\Seller\SellerAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('products', [App\Http\Controllers\Seller\ProductController::class, 'store']);
        Route::get('products', [App\Http\Controllers\Seller\ProductController::class, 'index']);
        Route::get('products/{id}/pdf', [App\Http\Controllers\Seller\ProductController::class, 'generatePdf']);
        Route::delete('products/{id}', [App\Http\Controllers\Seller\ProductController::class, 'destroy']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
