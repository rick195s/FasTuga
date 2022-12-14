<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Detailed\OrderDetailedResource;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Models\Order;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Order::class, 'order');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return OrderResource::collection(Order::orderBy('status')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return new OrderDetailedResource($order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'string|in:P,R,D,C',
            'delivered_by' => 'integer|exists:drivers,user_id',
        ]);


        if ($request->status && $order->status != 'D') {

            if ($order->customer && $validated['status'] == 'C') {
                $order->customer()->increment('points', $order->points_used_to_pay);
                $order->customer()->decrement('points', $order->points_gained);
            }

            $order->status = $validated['status'];
        }


        if ($request->delivered_by && $order->delivered_by == null) {
            $order->delivered_by = $validated['delivered_by'];
        }

        $order->save();
        return new OrderDetailedResource($order);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function toDeliver(Request $request)
    {
        $this->authorize('viewToDeliver', Order::class);
        return OrderResource::collection(Order::where('status', 'R')->orderBy('status')->paginate(10));
    }
}
