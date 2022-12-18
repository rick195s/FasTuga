<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductsPostRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ProductResource::collection(Product::orderBy('name')->paginate(10));
    }

    public function productType()
    {
        return Product::select('type')->distinct()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductsPostRequest $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validated();

        $ext = $validated['photo']->extension();
        $photoName =  uniqid() . '.' . $ext;
        $validated['photo']->storeAs('public/products', $photoName);

        $product = Product::create(
            [
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'],
                'price' => number_format((float) $validated['price'], 2, '.', ''),
                'photo_url' => $photoName,

            ]
        );

        return new ProductResource($product);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validated();

        $product->update($validated);

        return new ProductResource($product);
    }

    public function update_photo(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validated();

        if (isset($validated['photo'])) {
            $ext = $validated['photo']->extension();
            $photoName = $product->id . "_" . uniqid() . '.' . $ext;
            $validated['photo']->storeAs('public/products', $photoName);
            $product->photo_url = $photoName;
        }

        $product->save();

        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return new ProductResource($product);
    }
}
