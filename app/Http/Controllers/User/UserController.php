<?php

namespace App\Http\Controllers\User;

use App\User;
use App\Mail\UserCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\UserFormRequest;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{

    public function __construct()
    {
        $this->middleware('client.credentials')->only(['store','resend']);
        $this->middleware('auth:api')->except(['store','resend','verify']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->showAll(User::all(), 'users');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserFormRequest $request)
    {
        return $this->showOne(User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'verified' => User::USUARIO_NO_VERIFICADO,
            'verification_token' => User::generarToken(),
            'admin' => User::USUARIO_REGULAR
        ]), 'user', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user, 'user');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        if ($user->email != $request->email) {
            $user->verified = User::USUARIO_NO_VERIFICADO;
            $user->verification_token = User::generarToken();
            $user->email = $request->email;
        }
        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }
        if ($request->has('admin')) {
            if (!$user->esVerificado()) {
                return $this->errorResponse(['message' => 'Unicamente los usuarios verificados pueden cambiar su valor de administrador', 'code' => 409], 409);
            }
            $user->admin = $request->admin;
        }

        if (!$user->isDirty()) {
            return $this->errorResponse(['message' => 'Se debe especificar al menos un valor diferente para actualizar', 'code' => 422], 422);
        }
        $user->name = $request->name;
        $user->save();
        return response()->json(['user' => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return $this->showOne($user, 'user');
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();
        $user->verified = User::USUARIO_VERIFICADO;
        $user->verification_token = null;
        $user->save();
        return $this->anyMessage([
            'message' => 'La cuenta ha sido verificada'
        ]);
    }

    public function resend(User $user)
    {
        if ($user->esVerificado()) {
            return $this->errorResponse([
                'message' => 'Este usuario ya ha sido verificado',
                'code' => 409
            ],409);
        }
        retry(5, function() use ($user) {
            Mail::to($user)->send(new UserCreated($user));
        },100);

        return $this->anyMessage([
            'message' => 'Te hemos enviado un nuevo correo de verificacion'
        ]);
    }
}
