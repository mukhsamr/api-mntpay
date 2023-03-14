<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;


// Auth
Route::post('auth', [AuthController::class, 'authenticate']);


// Buyer
Route::middleware('auth:sanctum')->controller(BuyerController::class)->prefix('buyer')->group(function () {
    Route::get('/', 'index');
    Route::get('transaction-list', 'transactionList');
    Route::get('transaction-simple-list', 'transactionSimpleList');
    Route::post('check-pin', 'checkPin');
    Route::post('pay', 'store');
});


// Seller
Route::middleware('auth:sanctum')->controller(SellerController::class)->prefix('seller')->group(function () {
    Route::get('/', 'index');
    Route::get('transaction-list', 'transactionList');
    Route::get('transaction-simple-list', 'transactionSimpleList');
});


// Admin
Route::middleware('auth:sanctum')->controller(AdminController::class)->prefix('admin')->group(function () {
    Route::get('/', 'index');
    Route::get('transaction-list', 'transactionList');
    Route::get('transaction-simple-list', 'transactionSimpleList');
    Route::get('withdraw-list', 'withdrawList');
    Route::get('topup-list', 'topupList');
    Route::get('account-list/{tipe?}', 'accountList');

    Route::post('topup', 'storeTopup');

    Route::post('user-add', 'storeUser');
    Route::patch('user-edit', 'updateUser');
    Route::delete('user-delete', 'destroyUser');
});
