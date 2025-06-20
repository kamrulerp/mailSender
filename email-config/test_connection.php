<?php
require_once '../classes/Auth.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole(['admin', 'super_admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$smtp_host = trim($_POST['smtp_host'] ?? '');
$smtp_port = (int)($_POST['smtp_port'] ?? 587);
$smtp_username = trim($_POST['smtp_username'] ?? '');
$smtp_password = $_POST['smtp_password'] ?? '';
$encryption = $_POST['encryption'] ?? 'tls';
$from_email = trim($_POST['from_email'] ?? '');
$from_name = trim($_POST['from_name'] ?? '');

// Validation
if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password) || empty($from_email)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
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
    
    // Set encryption
    if ($encryption === 'ssl') {
        $mail->SMTPSecure = 'ssl';
    } elseif ($encryption === 'tls') {
        $mail->SMTPSecure = 'tls';
    }
    
    // Set timeout settings to prevent hanging
    $mail->Timeout = 30; // Connection timeout
    $mail->SMTPKeepAlive = false;
    
    // Disable debug output for testing
    $mail->SMTPDebug = 0;
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Test the connection
    if ($mail->smtpConnect()) {
        $mail->smtpClose();
        echo json_encode([
            'success' => true, 
            'message' => 'SMTP connection successful! Configuration is working properly.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to connect to SMTP server. Please check your settings.'
        ]);
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    
    // Clean up the error message for better user experience
    if (strpos($error_message, 'SMTP connect() failed') !== false) {
        $error_message = 'Cannot connect to SMTP server. Please check host and port settings.';
    } elseif (strpos($error_message, 'SMTP Error: Could not authenticate') !== false) {
        $error_message = 'SMTP authentication failed. Please check username and password.';
    } elseif (strpos($error_message, 'Connection timed out') !== false) {
        $error_message = 'Connection timed out. Please check host and port settings.';
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $error_message
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
}
?>