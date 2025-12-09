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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/post-login', [App\Http\Controllers\Apk\LoginController::class, 'postLogin']);
Route::prefix('user')->group(function () {
    Route::middleware(['auth:api', 'roleUser'])->group(function () {
        Route::get('/logout', [App\Http\Controllers\Apk\LoginController::class, 'logout']);

        Route::get('/scan-shipment', [App\Http\Controllers\Apk\ShipmentController::class, 'scanShipment']);
        Route::get('/shipment-check', [App\Http\Controllers\Apk\ShipmentController::class, 'check']);
        Route::post('/shipment-add', [App\Http\Controllers\Apk\ShipmentController::class, 'add']);
        Route::post('/shipment-delete', [App\Http\Controllers\Apk\ShipmentController::class, 'delete']);
        Route::post('/shipment-confirm', [App\Http\Controllers\Apk\ShipmentController::class, 'confirm']);

        Route::get('/scan-document', [App\Http\Controllers\Apk\DocumentController::class, 'scanDocument']);
        Route::post('/document-add', [App\Http\Controllers\Apk\DocumentController::class, 'add']);
        Route::post('/document-delete', [App\Http\Controllers\Apk\DocumentController::class, 'delete']);

        Route::get('/scan-code-product', [App\Http\Controllers\Apk\CodeProductController::class, 'scanCodeProduct']);
        Route::post('/code-product-add', [App\Http\Controllers\Apk\CodeProductController::class, 'add']);
        Route::post('/code-product-delete', [App\Http\Controllers\Apk\CodeProductController::class, 'delete']);

        Route::post('/shipdoc-add', [App\Http\Controllers\Apk\DocumentController::class, 'addShipDoc']);
    });
});
