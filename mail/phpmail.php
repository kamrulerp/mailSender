<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function mailer($to, $subject, $body) {
   $mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = 0; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host = '139.99.8.182';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@skitbd.com';
    $mail->Password = 'Akash@3707';
    $mail->SMTPSecure = 'tls'; // Use 'tls' instead of 'STARTLS'
    $mail->Port = 587; // Use 587 or 25 for STARTTLS
    $mail->setFrom('info@skitbd.com', 'SKIT');
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;

    // Disable SSL certificate verification (not recommended)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}

}


?>
