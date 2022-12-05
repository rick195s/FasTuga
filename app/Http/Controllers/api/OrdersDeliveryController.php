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
            $ordersToDeliver = $this->paginate($ordersToDeliver);
        }
        return OrderDriverDeliveryResource::collection($ordersToDeliver);


    }

    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    private function selectOrdersByFilter($ordersToDeliver, $filter){
        $ordersInFilter = array();
        $latitude = null;
        $longitude = null;
        $latitudeFastuga = 39.734730;
        $longitudeFastuga = -8.820921;
        
        foreach ($ordersToDeliver as $order) {
            $response = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$order->delivery_location);
            if($response->object()->data != null){
                $latitude = $response->object()->data[0]->latitude;
                $longitude = $response->object()->data[0]->longitude;
                //SE order <= filtro adicionar ELSE break 
                if($this->distance($latitude, $longitude, $latitudeFastuga, $longitudeFastuga, "K") <= $filter){
                    array_push($ordersInFilter,$order);
                }
            }/*else {
                array_push($ordersInFilter,"Failed to fing coordinates");
            }*/
        }
        return $ordersInFilter;
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
