<?php

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'takeshipereira25@gmail.com';
    $mail->Password = 'ktzw xjav ysag dnif';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom('takeshipereira25@gmail.com','Teste');
    $mail->addAddress('takeshipereira25@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'TESTE SMTP';
    $mail->Body = 'EMAIL FUNCIONANDO';

    $mail->send();

    echo "EMAIL ENVIADO";

} catch (Exception $e) {
    echo $mail->ErrorInfo;
}
