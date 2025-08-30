@component('mail::message')
# Hola {{ $user->name }}

Has solicitado restablecer tu contraseña en **Academia Internacional de Globos**.

@component('mail::button', ['url' => $url])
Cambiar Contraseña
@endcomponent

Si no solicitaste este cambio, puedes ignorar este correo.

Gracias,<br>
El equipo de Ballon Academy
@endcomponent
