<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\DriverResource;
use App\Http\Resources\OrderDriverDeliveryResource;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

    public function statistics(Driver $driver)
    {
        //starting a new collection
        $statisticsCollection = collect([]);

        //getting the orders delivered by the driver
        $ordersDelivered = DB::table('orders')
            ->where('delivered_by', $driver->user_id)
            ->where('status', 'D')
            ->count();

        //getting the distinct costumers that driver had delivered
        $distinctCostumers =
            DB::table('orders')
                ->where('delivered_by', $driver->user_id)
                ->where('status', 'D')
                ->count(DB::raw('DISTINCT customer_id')) +
            DB::table('orders')
                ->whereNull('customer_id')
                ->where('delivered_by', $driver->user_id)
                ->where('status', 'D')
                ->count();

        //getting the average time to deliver an order
        $averageTimeToDeliver = DB::table('orders')
            ->select(
                DB::raw(
                    'date_format(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(updated_at,created_at)))), "%i:%s") as timediff'
                )
            )
            ->where('delivered_by', $driver->user_id)
            ->where('status', 'D')
            ->get();

        //getting the total time delivering
        $totaTimeDelivering = DB::table('orders')
            ->select(
                DB::raw(
                    'SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(updated_at,created_at))))/360 as totaltimediff'
                )
            )
            ->where('delivered_by', $driver->user_id)
            ->where('status', 'D')
            ->groupBy('delivered_by')
            ->get();

        //adding the values to the collection
        $statisticsCollection->offsetSet('orders_delivered', $ordersDelivered);
        $statisticsCollection->offsetSet(
            'average_time_to_deliver',
            $averageTimeToDeliver[0]->timediff
        );
        $statisticsCollection->offsetSet(
            'total_time_delivering',
            (int) $totaTimeDelivering[0]->totaltimediff
        );
        $statisticsCollection->offsetSet(
            'distinct_costumers',
            $distinctCostumers
        );

        return $statisticsCollection;
    }
}
