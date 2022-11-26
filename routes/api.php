<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\DriverController;
use App\Http\Controllers\api\OrdersController;
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


Route::get("/orders", [OrdersController::class, "store"])->middleware('auth:api');



// An unknown user can register himself or the manager can register other users
Route::post("/register", [AuthController::class, "register"]);

// Just logged out users can login and register as a driver
Route::post("/login", [AuthController::class, "login"]);


Route::get("/me", [UserController::class, "show_me"])->middleware('auth:api');

// FasTugaDriver integration
Route::post("/register/driver", [AuthController::class, "registerDriver"]);
Route::post("/login/driver", [AuthController::class, "loginDriver"]);
