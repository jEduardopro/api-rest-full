<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UserFormRequest extends FormRequest
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
                        'email' => 'required|email|unique:users,email,id,:id',
                        'password' => 'required|min:6|confirmed',
                        'admin' => 'in:'.User::USUARIO_ADMINISTRADOR.','.User::USUARIO_REGULAR
                    ];
                break;

            default:
                    $rules = [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6|confirmed',
                    ];
                break;
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'El campo es obligatorio',
            'email.required' => 'El correo es obligatorio',
            'email.unique' => 'El correo que intentas usar ya esta registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe de contar con al menos 6 caracteres',
            'password.confirmed' => 'Es necesario confirmar la contraseña',
        ];
    }
}
