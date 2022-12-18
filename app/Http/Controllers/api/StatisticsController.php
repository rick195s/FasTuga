<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\DriverResource;
use App\Http\Resources\OrderDriverDeliveryResource;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Order;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{

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
        $time = Order::where('delivered_by', $driver->user_id)
            ->select(
                DB::raw("
                AVG(TIMESTAMPDIFF(SECOND, orders_driver_delivery.delivery_started_at, orders_driver_delivery.delivery_ended_at))
                AS timediff")
            )
            ->where('status', 'D')
            ->join('orders_driver_delivery', 'orders.id', '=', 'orders_driver_delivery.order_id')
            ->where('orders_driver_delivery.delivery_started_at', '!=', null)
            ->where('orders_driver_delivery.delivery_ended_at', '!=', null)
            ->get();
        return CarbonInterval::seconds((int)$time[0]->timediff)
            ->cascade()
            ->forHumans();
    }

    public function getTotalTimeDeliverings($driver)
    {
        //getting the total time delivering
        $time = DB::table('orders')
            ->select(
                DB::raw(
                    'SUM(TIMESTAMPDIFF(SECOND, orders_driver_delivery.delivery_started_at, orders_driver_delivery.delivery_ended_at))
                     as totaltimediff'
                )
            )
            ->where('delivered_by', $driver->user_id)
            ->where('status', 'D')
            ->join('orders_driver_delivery', 'orders.id', '=', 'orders_driver_delivery.order_id')
            ->where('orders_driver_delivery.delivery_started_at', '!=', null)
            ->where('orders_driver_delivery.delivery_ended_at', '!=', null)
            ->first();

        return CarbonInterval::seconds((int)$time->totaltimediff)
            ->cascade()
            ->forHumans();
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

    public function driverStatistics(Driver $driver)
    {
        $this->authorize('statistics', $driver);
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
            $averageTimeToDeliver
        );
        $statisticsCollection->offsetSet(
            'total_time_delivering',
            $totaTimeDelivering
        );
        $statisticsCollection->offsetSet(
            'distinct_costumers',
            $distinctCostumers
        );
        $statisticsCollection->offsetSet('balance', $balance);

        return $statisticsCollection;
    }

    public function mainStatistics()
    {
        $this->authorize('mainStatistics', User::class);
        return [
            "customers_count" => Customer::count(),
            "sales" => number_format(Order::where('status', 'D')->sum('total_price'), 0, ',', ','),
            "orders_count" => Order::count(),
        ];
    }
}
