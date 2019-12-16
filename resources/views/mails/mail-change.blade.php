@component('mail::message')
<h2>Hola {{ $user->name }}</h2>

Has cambiado tu correo electronico. Por favor verifica tu nueva cuenta de correo dando click en el siguiente boton

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Verificar Cuenta
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
