<?php
namespace Hcode;

use Rain\Tpl;

class Mailer {

    const USERNAME  = "takeshisaito83@gmail.com";
    const PASSWORD  = " ";
    const NAME_FROM = "Takeshi Saito";

    public function __construct(
        $toAddress,
        $toName,
        $subject,
        $tplName,
        $data = []
    ) {

        $mail = new \PHPMailer(true);

        $mail->isSMTP();
        $mail->SMTPDebug = 2;
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;

        $mail->Username = self::USERNAME;
        $mail->Password = self::PASSWORD;

        $mail->setFrom(self::USERNAME, self::NAME_FROM);
        $mail->addAddress($toAddress, $toName);

        $mail->Subject = $subject;

        $tpl = new Tpl;
        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName, true);

        $mail->msgHTML($html);
        $mail->AltBody = 'Para visualizar este email, use um cliente compatível com HTML';

        if (!$mail->send()) {
            throw new \Exception("Erro ao enviar email: " . $mail->ErrorInfo);
        }
    }
}
