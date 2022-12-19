<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OrderDriverDeliverySeeder extends Seeder
{
    private $driversIDs = [];
    private $numberOfOrders = 100;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (DatabaseSeeder::$seedType == "full") {
            $this->numberOfOrders = 600;
        } else {
            $this->numberOfOrders = 100;
        }

        $this->command->info("Order Driver Delivery seeder - Start");

        // Used to create Orders_Driver_Delivery (FasTuga Driver Integration)
        $this->command->info("Preparing Drivers");
        $this->driversIDs = Arr::pluck(DB::select("select user_id from drivers"), 'user_id');

        $faker = \Faker\Factory::create('pt_PT');


        $ordersAux = DB::table('orders')->select('id', 'delivered_by', 'created_at', 'status')
            ->whereIn('delivered_by', $this->driversIDs)->take($this->numberOfOrders)->get()->toArray();

        $this->command->info("Orders To Driver Delivery: " . count($ordersAux) . "");
        $countOrdersDriverDelivery  = 0;
        foreach ($ordersAux as $order) {
            $this->seedOrderDriverDelivery($faker, $order);
            $this->command->info("Entrega ao domicilio #" . $countOrdersDriverDelivery);
            $countOrdersDriverDelivery++;
        }

        $this->command->info("---- END ----");
    }

    // FastugaDriver
    public function seedOrderDriverDelivery($faker, $order)
    {
        $ordersDriverDelivery = [];

        // Check if order was delivered by a driver
        // if so we need to create the corresponding record
        // in orders_driver_delivery  (FasTuga Driver Integration)
        if (in_array($order->delivered_by, $this->driversIDs)) {
            $ordersDriverDelivery[] = $this->createOrdersDriverDeliveryArray($faker, $order->id, $order->created_at, $order->status);
            // (FasTuga Driver Integration)
            DB::table('orders_driver_delivery')->insert($ordersDriverDelivery);
            return 1;
        }
    }
    // Create the orders_driver_delivery record (FasTuga Driver Integration)
    private function createOrdersDriverDeliveryArray($faker, $id_order, $created_at, $order_status)
    {

        if ($order_status == 'R') {
            $inicio = null;
            $fim = null;
        } else {
            $inicio = Carbon::parse($created_at)->addSeconds(rand(39600, 78000));
            $fim = $inicio->copy()->addSeconds(rand(100, 900));
        }

        $tax_fee = Arr::random([2, 3, 5]);

        $fakerAddress = $faker->address;
        //$locationdata = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$fakerAddress)->object();
        $locationdata = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/" . $fakerAddress . ".json?access_token=" . env('MAPBOX_ACCESS_TOKEN'))
            ->object()->features[0]->center;

        if ($locationdata[0] != null && $locationdata[1] != null) {
            $latitude = $locationdata[1];
            $longitude = $locationdata[0];
        } else {
            $this->command->info("Is null");
            $latitude = 39.734730;
            $longitude = -8.820921;
        }

        //$this->command->info("OBJ: "+$locationdata+"\n");
        /*$object = $locationdata->object();*/
        /* $latitude = $locationdata->latitude;
        $this->command->info("LATITUDE-> ".$latitude);
        $longitude =  $locationdata->longitude;*/
        $latitudeFastuga = 39.734730;
        $longitudeFastuga = -8.820921;

        return [
            'order_id' => $id_order,
            'delivery_location' => $fakerAddress,
            'tax_fee' => $tax_fee,
            'delivery_started_at' => $inicio,
            'delivery_ended_at' => $fim,
            'distance' => $this->distance($latitude, $longitude, $latitudeFastuga, $longitudeFastuga, "K")
        ];
    }

    function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }
}
