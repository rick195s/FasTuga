<?php

use App\Http\Controllers\Auth\RegisterDriverController;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderDriverDelivery;
use App\Models\User;
use Illuminate\Http\Request;
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

Route::get("/", function () {
    return Driver::first()->ordersDriverDelivery->first()->order;
    // return User::has('orders')->get();
    //return User::has('order_items')->get();
});


Route::post("/register/driver", [RegisterDriverController::class, "store"]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
