<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailTemplate.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['template_id'])) {
    $template_id = (int)$_POST['template_id'];
    $emailTemplate = new EmailTemplate();
    
    $result = $emailTemplate->duplicateTemplate($template_id);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Template duplicated successfully',
            'new_id' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error duplicating template'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?>