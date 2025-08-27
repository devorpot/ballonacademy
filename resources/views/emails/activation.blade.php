<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Completa tu registro</title>
  <meta name="x-apple-disable-message-reformatting">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Evita fuentes externas para máxima compatibilidad -->
</head>
<body style="margin:0; padding:0; background-color:#f8f8f8;">
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f8f8f8; padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:600px; max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden;">
          <!-- Header con imagen -->
          <tr>
            <td align="center" style="padding:0; background:#0B2B60;">
              <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-internacional.png"
                   alt="Academia Internacional de Globos"
                 
                   style="display:block; width:100%; height:auto; border:0; outline:none; text-decoration:none;">
            </td>
          </tr>

          <!-- Contenido -->
          <tr>
            <td style="padding:32px 28px; color:#333333; font-family: Arial, Helvetica, sans-serif;">
              <h1 style="margin:0 0 12px; font-size:22px; line-height:1.3; color:#0b2e57;">Hola {{ $name }},</h1>

              <p style="margin:0 0 12px; font-size:16px; line-height:1.6;">
                Gracias por registrarte en <strong>Academia Internacional de Globos</strong>.
              </p>

              <p style="margin:0 0 18px; font-size:16px; line-height:1.6;">
                Para completar tu registro, activa tu cuenta dando clic en el siguiente botón:
              </p>

              <!-- Botón principal -->
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:24px auto;">
                <tr>
                  <td align="center" bgcolor="#e4ae4e" style="border-radius:6px;">
                    <a href="{{ $link }}"
                       style="display:inline-block; padding:14px 22px; font-size:16px; font-weight:bold; color:#ffffff; text-decoration:none; font-family: Arial, Helvetica, sans-serif;">
                      Activar tu cuenta Aquí
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 10px; font-size:16px; line-height:1.6;">
                Si el botón no funciona, copia y pega este enlace en tu navegador:
              </p>
              <p style="word-break:break-all; margin:0 0 20px; font-size:14px; color:#0b2e57;">
                <a href="{{ $link }}" style="color:#0b2e57; text-decoration:underline;">{{ $link }}</a>
              </p>

              <p style="margin:0 0 10px; font-size:16px; line-height:1.6;">
                También puedes ingresar manualmente tu código de activación:
              </p>

              <!-- Código resaltado -->
              <div style="text-align:center; margin:10px 0 24px;">
                <span style="display:inline-block; padding:12px 20px; background:#f2f2f2; border-radius:6px; font-size:22px; font-weight:bold; color:#0b2e57; letter-spacing:1px;">
                  {{ $code }}
                </span>
              </div>

              <p style="margin:0; font-size:15px; line-height:1.6;">
                Nos emociona darte la bienvenida y compartir esta experiencia contigo.
              </p>
            </td>
          </tr>
           <tr>
            <td align="center" style="padding:0; background:#0b2e57;">
              <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/card-pba_hover.png"
                   alt="Academia Internacional de Globos"
                   width="600"
                   style="display:block; width:100%; height:auto; border:0; outline:none; text-decoration:none;">
            </td>
          </tr>
          <!-- Footer -->
          <tr>
            <td style="background:#e4ae4e; padding:28px; text-align:center; color:#0b2e57; font-family: Arial, Helvetica, sans-serif;">
              <h3 style="margin:0 0 6px; font-size:16px; letter-spacing:0.3px;">CONTACTO</h3>
              <p style="margin:4px 0; font-weight:bold;">contacto@academiainternacionalglobos.com</p>
              <p style="margin:6px 0;">
                <a href="https://wa.link/y73aps" style="color:#0b2e57; text-decoration:none; font-weight:bold;">+52 332 834 3335</a>
              </p>

              <!-- Redes (texto para compatibilidad máxima) -->
              <p style="margin:10px 0;">
                <a href="https://www.facebook.com/AcademiaInternacionaldeGlobos" style="margin:0 8px; color:#0b2e57; text-decoration:underline;">Facebook</a>
                <a href="https://www.instagram.com/academiainternacionalglobos/" style="margin:0 8px; color:#0b2e57; text-decoration:underline;">Instagram</a>
                <a href="https://www.tiktok.com/@academiainterglobos" style="margin:0 8px; color:#0b2e57; text-decoration:underline;">TikTok</a>
                <a href="https://www.youtube.com/@AcademiaInternacionaldeGlobos" style="margin:0 8px; color:#0b2e57; text-decoration:underline;">YouTube</a>
              </p>

              <!-- Logos -->
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-top:14px;">
                <tr>
                  <td style="padding:0 8px;">
                    <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-fabricas.png"
                         alt="Fabricas"
                         height="36"
                         style="display:block; border:0; height:36px;">
                  </td>
                  <td style="padding:0 8px;">
                    <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-globos-payaso.png"
                         alt="Globos Payaso"
                         height="36"
                         style="display:block; border:0; height:36px;">
                  </td>
                  <td style="padding:0 8px;">
                    <img src="https://academiainternacionalglobos.com/wp-content/themes/academiaglobos/assets/images/logo-anafag.png"
                         alt="ANAFAG"
                         height="36"
                         style="display:block; border:0; height:36px;">
                  </td>
                </tr>
              </table>

              <p style="margin:12px 0 0; font-size:12px;">International Balloons Academy</p>
            </td>
          </tr>
        </table>

        <!-- Fallback de vista web (opcional) -->
        <p style="font-family: Arial, Helvetica, sans-serif; color:#8a8a8a; font-size:12px; margin:12px 0 0;">
          Si no solicitaste este correo, puedes ignorarlo.
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
