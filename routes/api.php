<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\DriverController;
use App\Http\Controllers\api\OrderItemsController;
use App\Http\Controllers\api\OrdersController;
use App\Http\Controllers\api\OrdersDeliveryController;
use App\Http\Controllers\api\ProductsController;
use App\Http\Controllers\api\StatisticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// An unknown user can register himself or the manager can register other users
Route::post('/register', [AuthController::class, 'register']);

// Just logged out users can login and register as a driver
Route::post('/login', [AuthController::class, 'login']);

Route::post('/register/driver', [AuthController::class, 'registerDriver']);
Route::post('/login/driver', [AuthController::class, 'loginDriver']);

Route::get('/products', [ProductsController::class, 'index']);
Route::get('/products/type', [ProductsController::class, 'productType']);

Route::post('/orders', [OrdersController::class, 'store']);
Route::get('/tickets', [OrdersController::class, 'tickets']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [UserController::class, 'show_me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // order items to be prepared from the chef
    Route::get('/orders/items', [OrderItemsController::class, 'index']);
    Route::put('/orders/items/{order_item}', [
        OrderItemsController::class,
        'update',
    ]);

    // orders to deliver
    Route::get('/orders/deliver', [OrdersController::class, 'toDeliver']);

    // -------------------------------------- FasTugaDriver integration

    Route::put('/drivers/{driver}', [DriverController::class, 'update']);
    // orders of a driver
    Route::get('/drivers/{driver}/orders', [DriverController::class, 'orders']);
    // history of orders of a customer
    Route::get('/customer/{customer}/orders', [CustomerController::class, 'ordersHistory']);

    //Statistics
    Route::get('drivers/{driver}/statistics', [StatisticsController::class, 'driverStatistics']);

    Route::get('statistics', [StatisticsController::class, 'mainStatistics']);



    // CRUD orders to driver delivery
    Route::get("/orders/drivers", [OrdersDeliveryController::class, "index"]);
    Route::put("/orders/drivers/{order_driver_delivery}/start_delivery", [OrdersDeliveryController::class, "start_delivery"]);
    Route::put("/orders/drivers/{order_driver_delivery}/end_delivery", [OrdersDeliveryController::class, "end_delivery"]);

    // --------------------------------------

    Route::patch('users/{user}/toggleBlocked', [UserController::class, 'toggle_blocked']);
    Route::post('users/{user}/photo', [UserController::class, 'update_photo']);

    //--------------------------------------

    Route::post('products/{product}/photo', [ProductsController::class, 'update_photo']);

    Route::apiResource('users', UserController::class);

    Route::apiResource('products', ProductsController::class)->except(['index', 'show']);

    Route::apiResource('orders', OrdersController::class)->except(['destroy', 'store']);
});
