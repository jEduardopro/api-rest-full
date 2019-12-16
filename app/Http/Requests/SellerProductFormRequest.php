<?php

namespace App\Http\Requests;

use App\Product;
use Illuminate\Foundation\Http\FormRequest;

class SellerProductFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'PUT':
            case 'PATCH':
                    $rules = [
                        'name' => 'required',
                        'description' => 'required',
                        'quantity' => 'required|integer|min:1',
                        'status' => 'in:'. Product::PRODUCTO_DISPONIBLE.','.Product::PRODUCTO_NO_DISPONIBLE,
                        'image' => 'image',
                    ];
                break;

            default:
                    $rules = [
                        'name' => 'required',
                        'description' => 'required',
                        'quantity' => 'required|integer|min:1',
                        'image' => 'required|image',
                    ];
                break;
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'El campo es obligatorio',
            'description.required' => 'El campo es obligatorio',
            'quantity.required' => 'El campo es obligatorio',
            'quantity.min' => 'El campo debe ser mayor a 0',
            'image.required' => 'El campo es obligatorio',
        ];
    }
}
