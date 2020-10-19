<?php

namespace Kugel\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailUtils {

    public static function enviarEmail($destinatarios, $assunto, $mensagem) {
        $mailer = new PHPMailer();
        $mailer->CharSet = 'UTF-8';

        $mailer->From = SecretUtils::getEmail();
        $mailer->Sender = SecretUtils::getEmail();
        $mailer->FromName = 'Suporte Kugel';
        $mailer->addReplyTo(SecretUtils::getEmail(), 'Suporte Kugel');
        $mailer->Subject = $assunto;
        $mailer->Body = $mensagem;

        $mailer->isHTML(true);

        $mailer->IsSMTP();
        $mailer->Host = SecretUtils::getHost();
        $mailer->Port = SecretUtils::getPort();
        
        $mailer->Mailer = 'smtp'; 
        $mailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ),
        );

        $mailer->SMTPAuth = true;
        $mailer->Username = SecretUtils::getEmail();
        $mailer->Password = SecretUtils::getPassword();

        foreach ($destinatarios as $dest) {
            $mailer->addAddress($dest);
        }

        $send_status = $mailer->send();
        if (!$send_status) {
            return $mailer->ErrorInfo;
        }
        
        return 'OK';
    }
}
