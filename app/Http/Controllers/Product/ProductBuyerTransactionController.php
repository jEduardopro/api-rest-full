<?php

namespace App\Http\Controllers\Product;

use App\User;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Transaction;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        if ($buyer->id == $product->seller_id) {
            return $this->errorResponse([
                'message' => 'El comprador debe ser diferente al vendedor',
                'code' => 409
            ], 409);
        }
        if (!$buyer->esVerificado()) {
            return $this->errorResponse([
                'message' => 'El comprador debe ser un usuario verificado',
                'code' => 409
            ], 409);
        }
        if (!$product->seller->esVerificado()) {
            return $this->errorResponse([
                'message' => 'El vendedor debe ser un usuario verificado',
                'code' => 409
            ], 409);
        }
        if (!$product->estaDisponible()) {
            return $this->errorResponse([
                'message' => 'El producto no esta disponible para esta transacción',
                'code' => 409
            ], 409);
        }
        if ($product->quantity < $request->quantity) {
            return $this->errorResponse([
                'message' => 'El producto no tiene la cantidad disponible requerida para esta transacción',
                'code' => 409
            ], 409);
        }

        return DB::transaction(function () use ($request, $product, $buyer) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);
            return $this->showOne($transaction, 'transaction', 201);
        });

    }
}
