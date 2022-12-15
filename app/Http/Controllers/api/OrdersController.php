<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\Detailed\OrderDetailedResource;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Order::class, 'order', [
            'except' => ['store']
        ]);
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
    public function store(CreateOrderRequest $request)
    {
        $validated = $request->validated();

        try {



            $body["type"] = strtolower($validated["payment_type"]);
            $body["reference"] = $validated["payment_reference"];

            // $request->total is not received from the request, but calculated in the CreateOrderRequest class
            $body["value"] = $validated["total"];

            $response = Http::post(env('PAYMENT_SYSTEM_URI') . 'payments', $body);

            if ($response->getStatusCode() == 422) {
                throw new \Exception('Error processing order.');
            }

            $order = new Order();

            $order->total_price = $validated["total"];
            $order->total_paid = $order->total_price;
            $order->total_paid_with_points = 0;
            $order->points_gained = 0;
            $order->points_used_to_pay = 0;

            $order->date = now();

            $user = auth()->guard('api')->user();
            if ($user && $user->customer) {
                if ($request->points_used_to_pay) {
                    $this->processCustomerPoints($user, $validated['points_used_to_pay'], $order);
                }
                $order->customer_id = $user->customer->id;
            }

            $order->ticket_number = $this->getTicketNumber();
            DB::beginTransaction();

            $user?->customer->save();
            $order->save();
            $this->createOrderItems($validated['items'], $order->id);
            DB::commit();

            return new OrderDetailedResource($order);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage(),
            ], 409);
        }
    }

    public function createOrderItems($items, $order_id)
    {
        $quantities = collect($items)->pluck('quantity', 'product_id');

        $products = Product::select('price', 'type', 'id')->whereIn('id', $quantities->keys())->get();

        $order_items = [];

        $order_local_number = 1;
        foreach ($products as $product) {
            for ($i = 0; $i < $quantities->get($product->id); $i++) {
                $order_items[] = [
                    'order_id' => $order_id,
                    'order_local_number' => $order_local_number,
                    'product_id' => $product->id,
                    'status' => $this->getOrderItemStatus($product->type),
                    'price' => $product->price,
                    'preparation_by' => $this->getPreparationBy($product->type),
                ];
                $order_local_number++;
            }
        }
        OrderItem::insert($order_items);
    }

    public function getPreparationBy($productType)
    {
        if ($productType == 'hot dish') {
            return User::where('type', 'EC')->inRandomOrder()->first()->id;
        }
        return null;
    }

    public function getOrderItemStatus($productType)
    {
        if ($productType == 'hot dish') {
            return 'W';
        }

        return 'R';
    }

    public function getTicketNumber()
    {
        $taken_tickets = Order::whereIn('status', ['P', 'R'])->pluck('ticket_number');
        if ($taken_tickets->count() >= 99) {
            throw new \Exception('The live limit of 99 orders has been reached.');
        }

        $available_tickets = collect()->range(1, 99)->diff($taken_tickets);
        return $available_tickets->first();
    }


    public function processCustomerPoints($user, $points, Order $order)
    {
        if ($user->customer->points < $points) {
            throw new \Exception('User has no enough points.');
        }

        $order->total_paid_with_points = $points / 10 * 5;

        if ($order->total_paid < $order->total_paid_with_points) {
            $order->total_paid = 0;
        } else {
            $order->total_paid -= $order->total_paid_with_points;
        }

        $user->customer->points -= $points;
        $order->points_used_to_pay = $points;

        $order->points_gained = (int) floor($order->total_price / 10);

        $user->customer->points += $order->points_gained;
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


        if ($request->status) {

            if ($order->customer && $validated['status'] == 'C') {
                try {
                    $this->refund($order);
                } catch (\Throwable $th) {
                    return response()->json([
                        'message' => 'Error while trying to refund the customer',
                    ], 422);
                }
            }

            if ($validated['status'] == 'D') {
                $order->delivered_by = auth()->user()->id;
            }

            $order->status = $validated['status'];
        }


        if ($request->delivered_by && $order->delivered_by == null) {
            $order->delivered_by = $validated['delivered_by'];
        }

        $order->save();
        return new OrderDetailedResource($order);
    }

    public function refund(Order $order)
    {
        $body["type"] = strtolower($order->customer->default_payment_type);
        $body["reference"] = $order->customer->default_payment_reference;
        $body["value"] = (float) $order->total_paid;

        $response = Http::post(env('PAYMENT_SYSTEM_URI') . 'refunds', $body);

        if ($response->getStatusCode() == 422) {
            throw new \Exception();
        } else {
            $order->customer()->increment('points', $order->points_used_to_pay);
            $order->customer()->decrement('points', $order->points_gained);
        }
    }


    public function toDeliver(Request $request)
    {
        $this->authorize('viewToDeliver', Order::class);
        return OrderResource::collection(Order::where('status', 'R')->orderBy('status')->paginate(10));
    }
}
