<?php

use App\Models\Customer;
use App\Models\Order;
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

// Route::get("/", function () {
//     return Customer::first()->user->name;
//     // return User::has('orders')->get();
//     //return User::has('order_items')->get();
// });


Route::post("/register", function () {
    return ["register"];
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
