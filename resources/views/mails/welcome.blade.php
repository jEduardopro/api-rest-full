@component('mail::message')
<h2>Hola {{ $user->name }}</h2>

Gracias por registrarte. Por favor verifica tu cuenta dando click en el siguiente boton.

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Verificar Cuenta
@endcomponent

Gracias por ser unirte a nuestra comunidad,<br>
{{ config('app.name') }}
@endcomponent
