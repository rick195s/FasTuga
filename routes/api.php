<?php

use App\Http\Controllers\Auth\LoginUserController;
use App\Http\Controllers\Auth\RegisterDriverController;
use App\Http\Resources\CustomerResource;
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
    return [new CustomerResource(Customer::first())];
    // return User::has('orders')->get();
    //return User::has('order_items')->get();
});


Route::post("/register/driver", [RegisterDriverController::class, "store"]);

Route::post("login", [LoginUserController::class, "login"]);
