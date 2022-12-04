<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\DriverController;
use App\Http\Controllers\api\OrdersController;
use App\Http\Controllers\api\OrdersDeliveryController;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
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


// -------------------------------------- FasTugaDriver integration
Route::post("/register/driver", [AuthController::class, "registerDriver"]);
Route::post("/login/driver", [AuthController::class, "loginDriver"]);


Route::middleware('auth:api')->group(function () {

    Route::put('/drivers/{driver}', [DriverController::class, 'update']);

    // orders of a driver
    Route::get('/drivers/{driver}/orders', [DriverController::class, 'orders']);

    // CRUD orders to driver delivery
    Route::get("/orders/drivers", [OrdersDeliveryController::class, "index"]);

    Route::get("/orders/drivers/{filter}", [OrdersDeliveryController::class, "index"]);

    Route::patch('users/{user}/toggleBlocked', [UserController::class, 'toggle_blocked'])->middleware('can:toggle_blocked,user');
    Route::post('users/{user}/photo', [UserController::class, 'update_photo'])->middleware('can:update,user');
    Route::apiResource("users", UserController::class);
});
