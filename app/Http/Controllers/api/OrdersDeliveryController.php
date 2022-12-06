<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderDriverDelivery;
use App\Models\Order;
use App\Http\Resources\OrderDriverDeliveryResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrdersDeliveryController extends Controller
{

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $ordersToDeliver = OrderDriverDelivery::whereNull("delivery_started_at")->paginate(10);
        if($request->filter != 'All'){
            $ordersToDeliver = $this->selectOrdersByFilter($ordersToDeliver,$request->filter);
           // $ordersToDeliver = $this->paginate($ordersToDeliver,$request->input('page'));
        }
        //return OrderDriverDeliveryResource::collection($ordersToDeliver);
        return $ordersToDeliver;
    }
    //READY TO SORT

    public function paginate($items,$page, $perPage = 10, $options = [])
    {
        $page = $page == null ? $page : 1;
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    private function selectOrdersByFilter($ordersToDeliver, $filter){
        $ordersInFilter = array();
        $latitude = null;
        $longitude = null;
        $latitudeFastuga = 39.734730;
        $longitudeFastuga = -8.820921;
        
        //foreach ($ordersToDeliver as $order) {
            //$response = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$order->delivery_location);
            /*if($response->object()->data != null){
                
            }else {
                array_push($ordersInFilter,"Failed to fing coordinates");
            }*/
        //}
        /*$ordersInFilter =*/
        return $this->orders_bubble_sort($ordersToDeliver,$latitudeFastuga, $longitudeFastuga);
        //return $ordersInFilter;
    }

    function orders_bubble_sort($arr,$latitudeFastuga, $longitudeFastuga) {
        $size = count($arr)-1;
        $dists = array();
        $i=0;
        foreach ($arr as $order) {
            
            $response = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$order->delivery_location);
            for ($j=0; $j<$size-$i; $j++) {
                $k = $j+1;
                $latitudeA = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$arr[$k]->delivery_location)->object()->data[0]->latitude;
                $longitudeA =  Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$arr[$k]->delivery_location)->object()->data[0]->longitude;
                $latitudeB = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$arr[$j]->delivery_location)->object()->data[0]->latitude;
                $longitudeB = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$arr[$j]->delivery_location)->object()->data[0]->longitude;
                //SE order <= filtro adicionar ELSE break 
                //$this->distance($latitude, $longitude, $latitudeFastuga, $longitudeFastuga, "K") < $filter
                if ($this->distance($latitudeA, $longitudeA, $latitudeFastuga, $longitudeFastuga, "K") > $this->distance($latitudeB, $longitudeB, $latitudeFastuga, $longitudeFastuga, "K")) {
                    // Swap elements at indices: $j, $k
                    array_push($dists,$this->distance($latitudeA, $longitudeA, $latitudeFastuga, $longitudeFastuga, "K"));
                    //list($arr[$j], $arr[$k]) = array($arr[$k], $arr[$j]);
                    
                }
            }
            $i=$i+1;
        }
        return $arr;
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
}
