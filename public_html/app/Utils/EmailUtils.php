<?php

namespace Kugel\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailUtils {

    public static function enviarEmail($destinatarios, $assunto, $mensagem) {
        $mailer = new PHPMailer();
        $mailer->IsSMTP();
        $mailer->Port = 465;
        $mailer->Host = 'smtp.gmail.com';
        $mailer->isHTML(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->Mailer = 'smtp'; 
        $mailer->SMTPSecure = 'ssl';

        $mailer->SMTPAuth = true;
        $mailer->Username = SecretUtils::getEmail();
        $mailer->Password = SecretUtils::getPassword();

        $mailer->From = SecretUtils::getEmail();
        $mailer->FromName = 'Ricardo Montania';

        foreach ($destinatarios as $dest) {
            $mailer->addAddress($dest);
        }

        $mailer->Subject = $assunto;
        $mailer->Body = $mensagem;
        
        return $mailer->send();
    }
}
