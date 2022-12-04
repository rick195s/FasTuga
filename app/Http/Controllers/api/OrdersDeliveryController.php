<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderDriverDelivery;
use App\Models\Order;
use App\Http\Resources\OrderDriverDeliveryResource;

class OrdersDeliveryController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $ordersToCollection = OrderDriverDelivery::whereNull("delivery_started_at")->paginate(10);

        /*foreach ($ordersToCollection as $order) {
            BingWebSearch($order->delivery_location)
            if(){

            }
        }*/
       /* if($request->filter == 'max.5km'){
            //get destination coordinates
            //get user coordinates

            return ["5km"];
        }
        if($request->filter == 'max.10km'){
            return ["10km"];
        }
        if($request->filter == 'max.15km'){
            return ["15km"];
        }*/
        return OrderDriverDeliveryResource::collection($ordersToCollection);


    }

    function BingWebSearch ($query) {
        /*$url = .env('API_URL')
        $key = .env('API_KEY')*/
        
        /* Prepare the HTTP request.
         * NOTE: Use the key 'http' even if you are making an HTTPS request.
         * See: http://php.net/manual/en/function.stream-context-create.php.
         */
        $headers = "Ocp-Apim-Subscription-Key: $key\r\n";
        $options = array ('http' => array (
                              'header' => $headers,
                               'method' => 'GET'));
    
        // Perform the request and get a JSON response.
        $context = stream_context_create($options);
        $result = file_get_contents($url . "?q=" . urlencode($query), false, $context);
    
        // Extract Bing HTTP headers.
        $headers = array();
        foreach ($http_response_header as $k => $v) {
            $h = explode(":", $v, 2);
            if (isset($h[1]))
                if (preg_match("/^BingAPIs-/", $h[0]) || preg_match("/^X-MSEdge-/", $h[0]))
                    $headers[trim($h[0])] = trim($h[1]);
        }
    
        return array($headers, $result);
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
