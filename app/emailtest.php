<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

include('phpmailer/6.9.1/src/Exception.php');
include('phpmailer/6.9.1/src/PHPMailer.php');
include('phpmailer/6.9.1/src/SMTP.php');
include('phpmailer/config/email_config.php');

function committeeEmails()
{
	$arrEmail = array('eizan.aziz@gmail.com' => 'eizan aziz', 
        'eizan@uitm.edu.my' => 'eizan aziz');
	return $arrEmail;
}

$mail = new PHPMailer(true);

try {
    //Server settings                                 
    $mail->isSMTP();                                            
    $mail->SMTPDebug = SMTP_DEBUG;                                                                              
    $mail->Host = SMTP_HOST; 
    $mail->SMTPAutoTLS = SMTP_AUTO_TLS;                                
    $mail->SMTPAuth = SMTP_AUTH; 
    $mail->SMTPSecure = SMTP_SECURE;                                
    $mail->Port = SMTP_PORT;                                   
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    //Recipients
    $mail->setFrom('eizan@uitm.edu.my', 'eizan aziz');
    foreach(committeeEmails() as $email => $name) // send to multiple receivers
    {
        $mail->addAddress($email, $name);
    }
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Sending test email from TREE-CAMUS 3';
    $body = "email testing from CSRAUB server " . date("Y-m-d H:i:s");
    $mail->msgHTML($body);
 
    $mail->send();
    echo "Notifikasi Emel Telah Dihantar.";
} catch (Exception $e) {
     echo "Notifikasi Emel Tidak Dapat Dihantar. Mailer Error: {$mail->ErrorInfo}";
}

?>
