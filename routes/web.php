<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('admin')->group(function () {
    Route::get('', [App\Http\Controllers\Web\LoginController::class, 'login'])->name('web.login');
    Route::post('/post-login', [App\Http\Controllers\Web\LoginController::class, 'postLogin'])->name('web.post-login');
    Route::middleware(['auth', 'roleAdmin'])->group(function () {
        Route::get('/logout', [App\Http\Controllers\Web\LoginController::class, 'logout'])->name('web.logout');

        Route::get('/shipment-list', [App\Http\Controllers\Web\ShipmentController::class, 'list'])->name('web.shipment.list');

        Route::get('/document-list', [App\Http\Controllers\Web\DocumentController::class, 'list'])->name('web.document.list');

        Route::get('/code-product-list', [App\Http\Controllers\Web\CodeProductController::class, 'list'])->name('web.code-product.list');
        Route::post('/code-product-delete', [App\Http\Controllers\Web\CodeProductController::class, 'delete'])->name('web.code-product.delete');

        Route::get('/user-list', [App\Http\Controllers\Web\UserController::class, 'list'])->name('web.user.list');
    });
});
