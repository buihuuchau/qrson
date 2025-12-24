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

Route::post('log', [App\Http\Controllers\LogAndDbController::class, 'log']);
Route::post('database', [App\Http\Controllers\LogAndDbController::class, 'database']);

Route::post('/post-login', [App\Http\Controllers\Apk\LoginController::class, 'postLogin']);
Route::prefix('user')->group(function () {
    Route::middleware(['auth:api', 'roleUser'])->group(function () {
        Route::get('/logout', [App\Http\Controllers\Apk\LoginController::class, 'logout']);

        Route::post('/add-data', [App\Http\Controllers\Apk\ShipmentController::class, 'addData']);
    });
});
