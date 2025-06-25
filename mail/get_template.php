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
    echo json_encode([
        'success' => true,
        'content' => $template['content'],
        'title' => $template['title']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Template not found or inactive']);
}
?>