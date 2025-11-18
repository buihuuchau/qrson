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
Route::prefix('admin')->middleware(['auth', 'roleAdmin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Web\LoginController::class, 'login'])->name('web.login');
    Route::post('/post-login', [App\Http\Controllers\Web\LoginController::class, 'postLogin'])->name('web.post-login');
    Route::get('/shipment-list', [App\Http\Controllers\Web\ShipmentController::class, 'list'])->name('web.shipment.list');
    Route::get('/document-list', [App\Http\Controllers\Web\DocumentController::class, 'list'])->name('web.document.list');
});