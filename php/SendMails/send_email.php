<?php
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía un correo utilizando PHPMailer con Gmail.
 *
 * @param string $destinatario Correo del destinatario.
 * @param string $nombreDestinatario Nombre del destinatario.
 * @param string $asunto Asunto del correo.
 * @param string $mensajeHTML Cuerpo del mensaje en formato HTML.
 * @param string $mensajePlano Cuerpo alternativo en texto plano.
 * @return bool true si se envió correctamente, false si hubo error.
 */
function enviarCorreo($destinatario, $nombreDestinatario, $asunto, $mensajeHTML, $mensajePlano) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aaventoneslocal@gmail.com';
        $mail->Password   = 'neub wuos hvtn ffsf'; // contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remitente y destinatario
        $mail->setFrom('aaventoneslocal@gmail.com', 'Aventones Local');
        $mail->addAddress($destinatario, $nombreDestinatario);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensajeHTML;
        $mail->AltBody = $mensajePlano;

        // Enviar correo
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}
