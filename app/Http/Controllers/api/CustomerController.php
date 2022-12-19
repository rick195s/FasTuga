<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Order;

class CustomerController extends Controller
{
    public function ordersHistory(Request $request, Customer $customer)
    {
        $this->authorize('ordersHistory', $customer);
        return OrderResource::collection(Order::where('customer_id', $customer->id)->orderBy('status')->paginate(10));
    }
}
