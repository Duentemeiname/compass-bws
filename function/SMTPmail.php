<?php

require_once('PHPMailer/class.phpmailer.php');
require_once('PHPMailer/class.smtp.php');


//Parameter für den E-Mail versand vorbereiten 
$mailhost       = "smtp.strato.de"; 
$mailsmtpauth   = true;
$mailusername   = "noreply@duentetech.de";
$mailpassword   = "etf=-D?fFH8p4A@";
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

//Zum Versand von Email:
// $mail->AddAddress($address, $adrname);
// $mail->Subject = "Subject";
// $mail->Body = "Der Mail Body"
// if(!$mail->Send()) {
//   echo "Mailer Error: " . $mail->ErrorInfo;
// } else {
// echo "Message sent!";
// }


?>