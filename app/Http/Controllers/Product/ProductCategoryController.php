<?php

namespace App\Http\Controllers\Product;

use App\Category;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ProductCategoryController extends ApiController
{
    public function __construct()
    {
        $this->middleware('client.credentials')->only(['index']);
        $this->middleware('auth:api')->except(['index']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        return $this->showAll(
            $product->categories,
            'product-categories'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product, Category $category)
    {
        // sync, attach, syncWithoutDetaching
        // El método sync no es útil en este ejemplo ya que borra todo los id repetidos del producto, dejando solo un unico registro
        // $product->categories()->sync([$category->id]);
        // El método attach tampoco es la mejor opcion ya que añade categorias existentes es decir el mismo producto tiene las mismas categorias duplicadas
        // $product->categories()->attach([$category->id]);
        // El método syncWithoutDetaching va a agregar la nueva categoria sin eliminar las anteriores ni duplicar las existentes
        $product->categories()->syncWithoutDetaching([$category->id]);
        return $this->showAll($product->categories,'categories');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, Category $category)
    {
        if (!$product->categories()->find($category->id)) {
            return $this->errorResponse([
                'message' => 'La categoria no esta asignada a este producto',
                'code' => 404
            ], 404);
        }
        $product->categories()->detach([$category->id]);
        return $this->showAll($product->categories, 'categories');
    }
}
