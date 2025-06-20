<?php
require_once '../classes/Auth.php';
require_once '../mail/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole(['admin', 'super_admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = (int)($_POST['smtp_port'] ?? 587);
    $smtp_username = trim($_POST['smtp_username'] ?? '');
    $smtp_password = $_POST['smtp_password'] ?? '';
    $smtp_encryption = $_POST['smtp_encryption'] ?? '';
    
    // Validation
    if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All SMTP fields are required'
        ]);
        exit;
    }
    
 
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->Port = $smtp_port;
        
        if ($smtp_encryption) {
            $mail->SMTPSecure = $smtp_encryption;
        }
        
        // Set timeout settings to prevent hanging
        $mail->Timeout = 30; // Connection timeout
        $mail->SMTPKeepAlive = false;
        
        // Disable SSL certificate verification to prevent connection issues
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Disable debug output for testing
        $mail->SMTPDebug = 0;
        
        // Test connection
        $mail->smtpConnect();
        $mail->smtpClose();
        
        echo json_encode([
            'success' => true,
            'message' => 'SMTP connection successful'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>