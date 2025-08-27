 
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Completa tu registro en Academia Internacional de Globos</title>
</head>
<body style="font-family: Arial, sans-serif; margin:0; padding:0; background-color:#f8f8f8;">

  <!-- Contenedor principal -->
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8f8f8; padding:20px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">
          <tr>
            <td align="center" style="padding:20px; background-color:#002f6c;">
              <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/card-pba_hover.png" alt="Academia Internacional de Globos" style="max-width:100%; height:auto;">
            </td>
          </tr>

          <!-- Contenido -->
          <tr>
            <td style="padding:30px; color:#333333; text-align:left;">
              <h2 style="color:#002f6c; margin-top:0;">Hola {{ $name }},</h2>
              <p style="font-size:16px; line-height:1.5;">
                Gracias por registrarte en <strong>Academia Internacional de Globos</strong>.
              </p>
              <p style="font-size:16px; line-height:1.5;">
                Para completar tu registro, activa tu cuenta dando clic en el siguiente botón:
              </p>

              <!-- Botón -->
              <p style="text-align:center; margin:30px 0;">
                <a href="{{ $link }}" 
                   style="background-color:#e4ae4e; color:#ffffff; padding:15px 25px; text-decoration:none; font-size:16px; font-weight:bold; border-radius:6px; display:inline-block;">
                  Activar tu cuenta Aquí
                </a>
              </p>

              <p style="font-size:16px; line-height:1.5;">
                O bien, puedes ingresar manualmente el siguiente código de activación:
              </p>

              <!-- Código resaltado -->
              <p style="text-align:center; font-size:22px; font-weight:bold; color:#002f6c; background:#f1f1f1; padding:10px; border-radius:6px;">
                {{ $code }}
              </p>

              <p style="font-size:15px; margin-top:25px; line-height:1.5;">
                ¡Nos emociona darte la bienvenida y compartir este proyecto contigo!
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#e4ae4e; padding:30px; text-align:center; color:#002f6c;">
              <h3 style="margin:0; font-size:18px;">CONTACTO</h3>
              <p style="margin:5px 0; font-weight:bold;">contacto@academiainternacionalglobos.com</p>
              <p style="margin:5px 0;">
                <a href="https://wa.link/y73aps" style="color:#002f6c; text-decoration:none; font-weight:bold;">+52 332 834 3335</a>
              </p>
              <p style="margin:10px 0;">
                <a href="https://www.facebook.com/AcademiaInternacionaldeGlobos" style="margin:0 8px; text-decoration:none; color:#002f6c;">Facebook</a>
                <a href="https://www.instagram.com/academiainternacionalglobos/" style="margin:0 8px; text-decoration:none; color:#002f6c;">Instagram</a>
                <a href="https://www.tiktok.com/@academiainterglobos" style="margin:0 8px; text-decoration:none; color:#002f6c;">TikTok</a>
                <a href="https://www.youtube.com/@AcademiaInternacionaldeGlobos" style="margin:0 8px; text-decoration:none; color:#002f6c;">YouTube</a>
              </p>

              <!-- Logos -->
              <p style="margin-top:20px;">
                <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-fabricas.png" alt="Fabricas" style="max-height:50px; margin:0 10px;">
                <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-globos-payaso.png" alt="Globos Payaso" style="max-height:50px; margin:0 10px;">
                <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-anafag.png" alt="ANAFAG" style="max-height:50px; margin:0 10px;">
              </p>

              <p style="margin-top:15px; font-size:13px;">International Balloons Academy</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
 
