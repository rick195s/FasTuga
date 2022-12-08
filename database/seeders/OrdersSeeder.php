<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OrdersSeeder extends Seeder
{
    private $numberOfDays = 100;
    private $avgOrdersDay = [260, 50, 65, 65, 120, 170, 200]; // Domingo, Segunda, terça, ...
    private $customerIDs = [];
    private $customerDetails = [];
    private $chefIDs = [];
    private $deliveryIDs = [];
    private $driversIDs = [];
    private $productIDs = [];
    private $productPrices = [];
    private $paymentTypes = ['VISA', 'PAYPAL', 'MBWAY'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DatabaseSeeder::$seedType == "full") {
            $this->numberOfDays = 2 * 365;   // 2 ANOS
        } else {
            $this->numberOfDays = 60;      // 2 meses
        }

        $this->command->info("Order seeder - Start");

        $this->command->info("Preparing Products");
        $prods = DB::select('select id, price from products');
        $this->productIDs = Arr::pluck($prods, 'id');
        $this->productPrices = Arr::pluck($prods, 'price', 'id');

        $this->command->info("Preparing Chefs");
        $this->chefIDs = Arr::pluck(DB::select("select id  from users where type = 'EC'"), 'id');

        $this->command->info("Preparing Delivery");
        $this->deliveryIDs = Arr::pluck(DB::select("select id  from users where type = 'ED'"), 'id');

        // Used to create Orders_Driver_Delivery (FasTuga Driver Integration)
        $this->command->info("Preparing Drivers");
        $this->driversIDs = Arr::pluck(DB::select("select user_id from drivers"), 'user_id');

        $this->command->info("Preparing Customers");
        $arrayCustomers = DB::select('select id, user_id, default_payment_type, default_payment_reference from customers');
        $this->customerIDs = Arr::pluck($arrayCustomers, 'id');
        $this->customerDetails = Arr::keyBy($arrayCustomers, 'id');

        $faker = \Faker\Factory::create('pt_PT');

        $today = Carbon::today();
        $this->start_date = $today->copy();
        $this->start_date->subDays($this->numberOfDays);
        $d = $this->start_date->copy();

        $i = 0;
        while ($d->lessThanOrEqualTo($today)) {
            if ($i % 20 == 0) { /// 20 em 20 dias escreve no log
                $this->command->info("Order for day " . $d->format('d-m-Y'));
            }
            if ($i % 100 == 0) { /// 100 em 100 dias negócio cresce (ou diminui)
                for ($j = 0; $j < count($this->avgOrdersDay); $j++)
                    foreach ($this->avgOrdersDay as $avg) {
                        $fatorCrescimento = rand(-3, 5);
                        $this->avgOrdersDay[$j] += $this->avgOrdersDay[$j] * $fatorCrescimento / 100;
                    }
            }
            $totalOrdersDay = intval($this->avgOrdersDay[$d->dayOfWeek] + $this->avgOrdersDay[$d->dayOfWeek] * rand(-20, 20) / 100);
            $totalOrdersDay = $totalOrdersDay < 0 ? 0 : $totalOrdersDay;
            $ordersDay = [];
            $ordersDriverDelivery = [];
            for ($num = 0; $num < $totalOrdersDay; $num++) {
                $ordersDay[] = $this->createOrderArray($faker, $d, $num);
            }
            DB::table('orders')->insert($ordersDay);

            $ordersAux = DB::table('orders')->select('id', 'delivered_by', 'created_at', 'status')
                ->where('date', $d->format('Y-m-d'))->get()->toArray();

            foreach ($ordersAux as $order) {
                $allItems = [];
                $total = $this->createOrderItemsArray($faker, $allItems, $order->id);
                DB::table('order_items')->insert($allItems);
                //DB::update('update orders set total_price = ? where id = ?', [$total, $id]);

                // Check if order was delivered by a driver
                // if so we need to create the corresponding record
                // in orders_driver_delivery  (FasTuga Driver Integration)
                if (in_array($order->delivered_by, $this->driversIDs)) {
                    $ordersDriverDelivery[] = $this->createOrdersDriverDeliveryArray($faker, $order->id, $order->created_at, $order->status);
                }
            }

            $this->command->info("Entregas ao domicilio:  " . count($ordersDriverDelivery));

            // (FasTuga Driver Integration)
            DB::table('orders_driver_delivery')->insert($ordersDriverDelivery);

            $i++;
            $d->addDays(1);
        }
        $this->command->info("Updating Orders Total Price");
        DB::update('update orders set total_price = (select sum(price) from order_items where order_items.order_id = orders.id)');
        DB::update("update orders set total_paid = total_price where status = 'D'");
        $this->command->info("Updating Orders and Customer Points");
        $this->seedPoints();

        $this->command->info("All Orders were created");
        $this->command->info("---- END ----");
    }

    private function createOrderArray($faker, $day, $orderNumberOfDay)
    {
        $inicio = $day->copy()->addSeconds(rand(39600, 78000));
        $fim = $inicio->copy()->addSeconds(rand(100, 900));

        $customerId = rand(0, 5) == 1 ? Arr::random($this->customerIDs) : null;
        if ($customerId) {
            $paymentType = $this->customerDetails[$customerId]->default_payment_type;
            $paymentRef = $this->customerDetails[$customerId]->default_payment_reference;
        } else {
            $paymentType = $faker->randomElement($this->paymentTypes);
            $paymentRef = UsersSeeder::getRandomPaymentReference($faker, $paymentType);
        }
        $status = rand(0, 40) == 1 ? 'R' : 'D';

        return [
            'status' => $status,
            'customer_id' => $customerId,
            'ticket_number' => $orderNumberOfDay % 99 + 1,
            'total_price' => 0,
            'total_paid' => 0,
            'total_paid_with_points' => 0,
            'payment_type' => $paymentType,
            'payment_reference' => $paymentRef,
            'points_gained' => 0,
            'points_used_to_pay' => 0,
            'date' =>  $day->format('Y-m-d'),
            'delivered_by' => Arr::random($this->deliveryIDs),
            'created_at' => $inicio,
            'updated_at' => $fim
        ];
    }

    private function createOrderItemsArray($faker, &$allItems, $id_order)
    {
        $totalItems = rand(1, 10);
        for ($i = 0; $i < $totalItems; $i++) {
            $prodID = Arr::random($this->productIDs);
            $allItems[] = [
                'order_id' => $id_order,
                'order_local_number' => $i + 1,
                'product_id' => $prodID,
                'status' => 'R',
                'price' => $this->productPrices[$prodID],
                'preparation_by' => Arr::random($this->chefIDs),
                'notes' => rand(0, 20) == 1 ? $faker->realText(100) : null,
            ];
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
        $locationdata = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$fakerAddress)->object();
        if($locationdata->data != null && $locationdata->data[0] != null ){
            $latitude = $locationdata->data[0]->latitude;
            $longitude = $locationdata->data[0]->longitude;
        }else{
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

    function distance($lat1, $lon1, $lat2, $lon2, $unit) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
          return 0;
        }
        else {
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

    private function seedPoints()
    {
        $allOrdersOfCustomers = DB::select("select id, total_price div 10 as points from orders where customer_id is not null and status = 'D' order by id");
        foreach ($allOrdersOfCustomers as $order) {
            DB::update("update orders set points_gained = ? where id = ?", [$order->points, $order->id]);
        }

        $allCustomers = DB::select("select customer_id, sum(points_gained) as total_points from orders where customer_id is not null and status = 'D' group by customer_id order by customer_id");
        foreach ($allCustomers as $customer) {
            DB::update("update customers set points = ? where id = ?", [$customer->total_points, $customer->customer_id]);
        }

        $customersWithExtraPoints = DB::select("select id, points from customers where points >= 10");
        foreach ($customersWithExtraPoints as $customer) {
            $totalPoints = $customer->points;
            $totalOrders = intdiv($totalPoints - 10, 10);
            $ordersToChange = DB::select(
                "select id, total_price, points_gained from orders where customer_id = ? and status = 'D' and total_price > 5 order by created_at desc limit ?",
                [$customer->id, $totalOrders]
            );
            foreach ($ordersToChange as $order) {
                $oldPrice = $order->total_price;
                $oldPoints = $order->points_gained;
                $usePointTen = min(intdiv($oldPrice, 5), $totalOrders);
                $totalOrders = $totalOrders - $usePointTen;
                $totalPaidWithPoints = $usePointTen * 5;
                $newPrice = $oldPrice - $totalPaidWithPoints;
                $newPoints = intdiv($newPrice, 10);
                DB::update(
                    "update orders set total_paid_with_points = ?, total_paid = ?, points_gained = ?, points_used_to_pay = ? where id = ?",
                    [$totalPaidWithPoints, $oldPrice - $totalPaidWithPoints, $newPoints, $usePointTen * 10, $order->id]
                );
                DB::update("update customers set points = points + ? where id = ?", [$newPoints - $oldPoints - $usePointTen * 10, $customer->id]);
                if ($totalOrders <= 0) {
                    break;
                }
            }
            DB::update("update customers set points = 0 where points < 0");
        }
    }
}
