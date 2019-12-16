<?php

namespace App\Http\Controllers\Seller;

use App\User;
use App\Seller;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\SellerProductFormRequest;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        return $this->showAll(
            $seller->products,
            'seller-products'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SellerProductFormRequest $request, User $seller)
    {
        $data = $request->all();
        $data['status'] = Product::PRODUCTO_NO_DISPONIBLE;
        $data['image'] = $request->image->store('');
        $data['seller_id'] = $seller->id;
        return $this->showOne(Product::create($data), 'product', 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(SellerProductFormRequest $request, Seller $seller, Product $product)
    {
        $this->verificarVendedor($seller, $product);
        $product->fill($request->only([
            'name',
            'description',
            'quantity'
        ]));

        if ($request->has('status')) {
            $product->status = $request->status;
            if ($product->estaDisponible() && $product->categories()->count() == 0) {
                return $this->errorResponse([
                    'message' => 'Un producto activo debe tener al menos una categoria',
                    'code' => 409
                ], 409);
            }
        }
        if ($request->hasFile('image')) {
            Storage::delete($product->image);
            $product->image = $request->image->store('');
        }

        if ($product->isClean()) {
            return $this->errorResponse([
                'message' => 'Sebe especificar al menos un valor diferente para actualizar',
                'code' => 422
            ], 422);
        }
        $product->save();
        return $this->showOne($product, 'product');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        $this->verificarVendedor($seller, $product);
        Storage::delete($product->image);
        $product->delete();
        return $this->showOne($product, 'product');
    }

    protected function verificarVendedor(Seller $seller, Product $product)
    {
        if ($seller->id != $product->seller_id) {
            throw new HttpException(403, 'No tienes permisos para realizar esta accion');
        }
    }
}
