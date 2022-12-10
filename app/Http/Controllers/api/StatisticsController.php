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

    public function getOrdersDeliveredByDriver($driver)
    {
        //getting the orders delivered by the driver
        return DB::table('orders')
            ->where('delivered_by', $driver->user_id)
            ->where('status', 'D')
            ->count();
    }

    public function getDistinctCostumers($driver)
    {
        //getting the distinct costumers that driver had delivered
        return DB::table('orders')
            ->where('delivered_by', $driver->user_id)
            ->where('status', 'D')
            ->count(DB::raw('DISTINCT customer_id')) +
            DB::table('orders')
                ->whereNull('customer_id')
                ->where('delivered_by', $driver->user_id)
                ->where('status', 'D')
                ->count();
    }

    public function getAverageTimeToDeliver($driver)
    {
        //getting the average time to deliver an order
        return DB::table('orders')
            ->select(
                DB::raw(
                    'date_format(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(updated_at,created_at)))), "%i:%s") as timediff'
                )
            )
            ->where('delivered_by', $driver->user_id)
            ->where('status', 'D')
            ->get();
    }

    public function getTotalTimeDeliverings($driver)
    {
        //getting the total time delivering
        return DB::table('orders')
            ->select(
                DB::raw(
                    'SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(updated_at,created_at))))/360 as totaltimediff'
                )
            )
            ->where('delivered_by', $driver->user_id)
            ->where('status', 'D')
            ->groupBy('delivered_by')
            ->get();
    }

    public function getBalance($driver)
    {
        //getting the balance of the driver
        return DB::table('orders_driver_delivery')
            ->select('orders_driver_delivery.tax_fee')
            ->join(
                'orders',
                'orders.id',
                '=',
                'orders_driver_delivery.order_id'
            )
            ->where('orders.delivered_by', $driver->user_id)
            ->where('orders.status', 'D')
            ->get()
            ->sum('tax_fee');
    }

    public function statistics(Driver $driver)
    {
        //starting a new collection
        $statisticsCollection = collect([]);

        //getting the orders delivered by the driver
        $ordersDelivered = $this->getOrdersDeliveredByDriver($driver);

        //getting the distinct costumers that driver had delivered
        $distinctCostumers = $this->getDistinctCostumers($driver);

        //getting the average time to deliver an order
        $averageTimeToDeliver = $this->getAverageTimeToDeliver($driver);

        //getting the total time delivering
        $totaTimeDelivering = $this->getTotalTimeDeliverings($driver);

        //getting the balance of the driver
        $balance = $this->getBalance($driver);

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
        $statisticsCollection->offsetSet('balance', $balance);

        return $statisticsCollection;
    }
}
