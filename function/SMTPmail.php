<?php

require_once('PHPMailer/class.phpmailer.php');
require_once('PHPMailer/class.smtp.php');
require_once('DBconfig.php');


//Parameter für den E-Mail versand vorbereiten 
$mailhost       = "smtp.strato.de"; 
$mailsmtpauth   = true;
$mailusername   = "noreply@duentetech.de";
$mailpassword   = "xxxxxxxx";
$mailsmtpsecure = "tls";
$mailsmtpport = 587;

$mail = new PHPMailer();
$mail->IsSMTP();
$mail->CharSet ="UTF-8";
$mail->Host = $mailhost;
$mail->SMTPDebug  = 0;
$mail->SMTPAuth = true;
$mail->SMTPSecure = $mailsmtpsecure;
$mail->Username = $mailusername;
$mail->Password = $mailpassword;
$mail->Port = $mailsmtpport;
$mail->setFrom('noreply@duentetech.de', 'Compass Brühlwiesenschule');

function sendEmail($EMail, $Name, $Betreff, $Inhalt)
{
    global $mail, $db_link, $uhrzeit, $infos_user, $user_ip;

    $mail->addAddress($EMail, $Name);
    $mail->isHTML();
    $mail->Subject = $Betreff;
    $mail->Body    = $Inhalt;
    if(!$mail->Send()) 
    {
    //Fehlgeschlagene E-Mail Dokumentieren wird in der DB gespeichert
    $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
                VALUES ( '$user_ip', 'E-Mail senden an $EMail gescheitert. Grund: $mail->ErrorInfo', '$EMail', '$uhrzeit', '$infos_user')";
                                           
    $db_link->query($Anfrage);   
    }
    $mail->clearAddresses();
    $mail->clearAllRecipients();
    $mail->clearAttachments();
}

?>