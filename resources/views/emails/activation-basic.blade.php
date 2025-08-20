<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Activa tu cuenta</title>
</head>
<body style="font-family: Arial, sans-serif; color:#222;">
  <h2>Bienvenido(a) a Ballon Academy</h2>
  <p>Hola {{ $user->name }},</p>
  <p>Tu cuenta ha sido creada. Para activarla, haz clic en el siguiente enlace:</p>
  <p>
    <a href="{{ $activationUrl }}" style="background:#2F68FF;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;">
      Activar cuenta
    </a>
  </p>

  <h3>Acceso</h3>
  <ul>
    <li><strong>Email:</strong> {{ $user->email }}</li>
    @isset($password)
      <li><strong>Password temporal:</strong> {{ $password }}</li>
      <li>Por seguridad te recomendamos cambiarlo al iniciar sesión.</li>
    @endisset
  </ul>

  <p style="color:#666;font-size:12px;">
    Este enlace expira en 48 horas. Si no fuiste tú quien solicitó esta activación, puedes ignorar este mensaje.
  </p>
</body>
</html>
