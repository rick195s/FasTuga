<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Detailed\OrderItemDetailedResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', OrderItem::class);

        return OrderItemDetailedResource::collection(
            auth()->user()->orderItems()
                ->whereRelation('product', 'type', '=', 'hot dish')
                ->whereRelation('order', 'status', '=', 'P')
                ->where(function ($query) {
                    $query->where('status', 'W')
                        ->orWhere('status', 'P');
                })
                ->orderBy('status', 'desc')->paginate(10)
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderItem  $orderItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderItem $orderItem)
    {
        $this->authorize('update', $orderItem);
        $validated = $request->validate([
            'status' => 'string|in:P,W,R',
        ]);

        $orderItem->status = $validated['status'];
        $orderItem->save();

        // verify if order can go to ready status
        if ($validated['status'] == 'R') {
            $this->orderReady($orderItem->order);
        }

        return new OrderItemDetailedResource($orderItem);
    }

    public function orderReady(Order $order)
    {
        $canBeReady = true;

        $orderItems = $order->orderItems();
        foreach ($orderItems as  $item) {
            if ($item->status != 'R') {
                $canBeReady = false;
                break;
            }
        }
        if ($canBeReady) {
            $order->status = 'R';
            $order->save();
        }
    }
}
