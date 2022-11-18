<?php

use App\Http\Controllers\Auth\LoginUserController;
use App\Http\Controllers\Auth\RegisterUserController;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\DriverResource;
use App\Http\Resources\OrderDriverDeliveryResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderDriverDelivery;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    return new DriverResource(Driver::first());
    // return User::has('orders')->get();
    //return User::has('order_items')->get();
});


Route::get("/orders", function () {
    return Auth::user();
})->middleware("auth:api");


Route::post("/login", [LoginUserController::class, "user"]);

Route::post("/login/driver", [LoginUserController::class, "driver"]);
Route::post("/register/driver", [RegisterUserController::class, "driver"]);
