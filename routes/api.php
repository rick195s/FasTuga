<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\DriverController;
use App\Http\Controllers\api\OrdersController;
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
Route::post("/register", [AuthController::class, "register"]);

// Just logged out users can login and register as a driver
Route::post("/login", [AuthController::class, "login"]);

Route::middleware('auth:api')->group(function () {
    Route::get("/me", [UserController::class, "show_me"]);
    Route::post('/logout', [AuthController::class, 'logout']);
});


// FasTugaDriver integration
Route::post("/register/driver", [AuthController::class, "registerDriver"]);
Route::post("/login/driver", [AuthController::class, "loginDriver"]);


Route::middleware('auth:api')->group(function () {

    Route::put('/drivers/{driver}', [DriverController::class, 'update']);
    Route::get("/orders", [OrdersController::class, "ordersToDriver"]);
});
