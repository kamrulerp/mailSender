<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailTemplate.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['template_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$template_id = $_POST['template_id'];
$emailTemplate = new EmailTemplate();

$template = $emailTemplate->getTemplateById($template_id);

if ($template && $template['status'] == 'active') {
    $content = $template['content'];
    
    // Replace template variables if provided
    if (isset($_POST['recipient_name'])) {
        $content = str_replace('{{recipient_name}}', $_POST['recipient_name'], $content);
    }
    
    if (isset($_POST['passport_number'])) {
        $content = str_replace('{{passport_number}}', $_POST['passport_number'], $content);
    }
    
    if (isset($_POST['location'])) {
        $content = str_replace('{{address}}', $_POST['location'], $content);
    }

    if(isset($_POST['designation'])){
        $content = str_replace('{{designation}}', $_POST['designation'], $content);
    }
    if(isset($_POST['company_name'])){
        $content = str_replace('{{company}}', $_POST['company_name'], $content);
    }
    if(isset($_POST['salary'])){
        $content = str_replace('{{salary}}', $_POST['salary'], $content);
    }
    if(isset($_POST['date'])){
        $content = str_replace('{{date}}', $_POST['date'], $content);
    }
    
    echo json_encode([
        'success' => true,
        'content' => $content,
        'title' => $template['title']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Template not found or inactive']);
}
?>