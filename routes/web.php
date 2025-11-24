<?php

use Illuminate\Support\Facades\Route;

Route::get('', [App\Http\Controllers\Web\LoginController::class, 'login'])->name('web.login');
Route::post('/post-login', [App\Http\Controllers\Web\LoginController::class, 'postLogin'])->name('web.post-login');

Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [App\Http\Controllers\Web\LoginController::class, 'logout'])->name('web.logout');
    Route::middleware(['roleAdmin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/shipment-list', [App\Http\Controllers\Web\ShipmentController::class, 'list'])->name('web.shipment.list');
            Route::post('/shipment-export', [App\Http\Controllers\Web\ShipmentExportController::class, 'exportShipment'])->name('web.shipment.export');
            Route::post('/shipment-delete', [App\Http\Controllers\Web\ShipmentController::class, 'delete'])->name('web.shipment.delete');

            Route::get('/document-list', [App\Http\Controllers\Web\DocumentController::class, 'list'])->name('web.document.list');
            Route::post('/document-delete', [App\Http\Controllers\Web\DocumentController::class, 'delete'])->name('web.document.delete');

            Route::get('/code-product-list', [App\Http\Controllers\Web\CodeProductController::class, 'list'])->name('web.code-product.list');
            Route::post('/code-product-delete', [App\Http\Controllers\Web\CodeProductController::class, 'delete'])->name('web.code-product.delete');

            Route::get('/user-list', [App\Http\Controllers\Web\UserController::class, 'list'])->name('web.user.list');
            Route::post('/user-add', [App\Http\Controllers\Web\UserController::class, 'add'])->name('web.user.add');
            Route::post('/user-update', [App\Http\Controllers\Web\UserController::class, 'update'])->name('web.user.update');
            Route::post('/user-delete', [App\Http\Controllers\Web\UserController::class, 'delete'])->name('web.user.delete');
        });
    });


    Route::middleware(['roleUser'])->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/scan-shipment', [App\Http\Controllers\User\ShipmentController::class, 'scanShipment'])->name('user.scan.shipment');
            Route::get('/shipment-check', [App\Http\Controllers\User\ShipmentController::class, 'check'])->name('user.shipment.check');
            Route::post('/shipment-add', [App\Http\Controllers\User\ShipmentController::class, 'add'])->name('user.shipment.add');
            Route::post('/shipment-delete', [App\Http\Controllers\User\ShipmentController::class, 'delete'])->name('user.shipment.delete');
            Route::post('/shipment-confirm', [App\Http\Controllers\User\ShipmentController::class, 'confirm'])->name('user.shipment.confirm');

            Route::get('/scan-document', [App\Http\Controllers\User\DocumentController::class, 'scanDocument'])->name('user.scan.document');
            Route::get('/document-check', [App\Http\Controllers\User\DocumentController::class, 'check']);
            Route::post('/document-add', [App\Http\Controllers\User\DocumentController::class, 'add']);
            Route::post('/document-delete', [App\Http\Controllers\User\DocumentController::class, 'delete']);

            Route::get('/scan-code-product', [App\Http\Controllers\User\CodeProductController::class, 'scanCodeProduct'])->name('user.scan.codeProduct');
            Route::post('/code-product-add', [App\Http\Controllers\User\CodeProductController::class, 'add']);
            Route::post('/code-product-delete', [App\Http\Controllers\User\CodeProductController::class, 'delete']);
        });
    });
});
