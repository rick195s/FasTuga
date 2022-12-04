<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderDriverDelivery;
use App\Models\Order;
use App\Http\Resources\OrderDriverDeliveryResource;
use Illuminate\Support\Facades\Http;

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

        if($request->filter == 'max.5km'){
            $ordersToDeliver = $this->selectOrdersByFilter($ordersToDeliver,5);
        }
        if($request->filter == 'max.10km'){
            $ordersToDeliver = $this->selectOrdersByFilter($ordersToDeliver,10);
        }
        if($request->filter == 'max.15km'){
            $ordersToDeliver = $this->selectOrdersByFilter($ordersToDeliver,15);
        }
        return $ordersToDeliver;


    }

    private function selectOrdersByFilter($ordersToDeliver, $filter){
        $ordersInFilter = array();
        $latitude = null;
        $longitude = null;
        foreach ($ordersToDeliver as $order) {
            $response = Http::get('http://api.positionstack.com/v1/forward?access_key=ce376ccadaa61d0f359a19b28d856659&query='.$order->delivery_location);
            if($response->object()->data != null){
                $latitude = $response->object()->data[0]->latitude;
                $longitude = $response->object()->data[0]->longitude;
                //Calculate distance with coordinates
                //SE order <= filtro adicionar ELSE break 
                array_push($ordersInFilter,$order);
            }else {
                array_push($ordersInFilter,"Failed to fing coordinates");
            }
        }
        return $ordersInFilter;
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
