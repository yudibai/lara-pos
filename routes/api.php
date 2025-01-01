<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\BankAccountController;
 
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::middleware([ForceJsonResponse::class])->group(function () {
        Route::post('/v1/register', [AuthController::class, 'register'])->name('register');
        Route::post('/v1/login', [AuthController::class, 'login'])->name('login');
        Route::post('/v1/refresh-login', [AuthController::class, 'refreshLogin'])->name('refreshLogin');
        Route::post('/v1/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
        Route::post('/v1/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
        Route::post('/v1/userdata', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
        Route::post('/v1/user-delete', [AuthController::class, 'delete'])->middleware('auth:api')->name('user-delete');
        
        // log
        Route::get('/v1/logs', [LogController::class, 'store'])->name('logs');
        Route::post('/v1/create-log', [LogController::class, 'store'])->name('create-log');
        
        // place
        Route::get('/v1/places', [PlaceController::class, 'store'])->middleware('auth:api')->name('get-place');
        Route::post('/v1/place-id', [PlaceController::class, 'storeById'])->middleware('auth:api')->name('get-place-id');
        Route::post('/v1/create-place', [PlaceController::class, 'store'])->middleware('auth:api')->name('create-place');
        Route::post('/v1/update-place', [PlaceController::class, 'store'])->middleware('auth:api')->name('update-place');
        Route::post('/v1/delete-place', [PlaceController::class, 'delete'])->middleware('auth:api')->name('delete-place');

        // product
        Route::get('/v1/products', [ProductController::class, 'store'])->middleware('auth:api')->name('get-product');
        Route::post('/v1/create-product', [ProductController::class, 'store'])->middleware('auth:api')->name('create-product');
        Route::post('/v1/update-product', [ProductController::class, 'store'])->middleware('auth:api')->name('update-product');
        Route::post('/v1/delete-product', [ProductController::class, 'delete'])->middleware('auth:api')->name('delete-product');
        
        // product category
        Route::get('/v1/product-categories', [ProductCategoryController::class, 'store'])->middleware('auth:api')->name('get-product-category');
        Route::post('/v1/create-product-category', [ProductCategoryController::class, 'store'])->middleware('auth:api')->name('create-product-category');
        Route::post('/v1/update-product-category', [ProductCategoryController::class, 'store'])->middleware('auth:api')->name('update-product-category');
        Route::post('/v1/delete-product-category', [ProductCategoryController::class, 'delete'])->middleware('auth:api')->name('delete-product-category');
        
        // customer
        Route::get('/v1/customers', [CustomerController::class, 'store'])->middleware('auth:api')->name('get-customer');
        Route::post('/v1/create-customer', [CustomerController::class, 'store'])->middleware('auth:api')->name('create-customer');
        Route::post('/v1/update-customer', [CustomerController::class, 'store'])->middleware('auth:api')->name('update-customer');
        Route::post('/v1/delete-customer', [CustomerController::class, 'delete'])->middleware('auth:api')->name('delete-customer');
        
        // cashier
        Route::get('/v1/product-cashier', [CashierController::class, 'store'])->middleware('auth:api')->name('get-product-cashier');

        // bank account
        Route::get('/v1/bank-accounts', [BankAccountController::class, 'store'])->middleware('auth:api')->name('get-bank-accounts');
        Route::post('/v1/create-bank-account', [BankAccountController::class, 'store'])->middleware('auth:api')->name('create-bank-account');
        Route::post('/v1/update-bank-account', [BankAccountController::class, 'store'])->middleware('auth:api')->name('update-bank-account');
        Route::post('/v1/delete-bank-account', [BankAccountController::class, 'delete'])->middleware('auth:api')->name('delete-bank-account');
    });
});