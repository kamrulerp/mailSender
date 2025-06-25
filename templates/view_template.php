<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailTemplate.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['template_id'])) {
    $template_id = (int)$_POST['template_id'];
    $emailTemplate = new EmailTemplate();
    
    $template = $emailTemplate->getTemplateById($template_id);
    
    if ($template) {
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6><strong>Title:</strong></h6>';
        echo '<p>' . htmlspecialchars($template['title']) . '</p>';
        
        echo '<h6><strong>Status:</strong></h6>';
        if ($template['status'] == 'active') {
            echo '<span class="badge bg-success">Active</span>';
        } else {
            echo '<span class="badge bg-secondary">Inactive</span>';
        }
        
        echo '<h6 class="mt-3"><strong>Created By:</strong></h6>';
        echo '<p>' . htmlspecialchars($template['created_by_name'] ?? 'Unknown') . '</p>';
        
        echo '<h6><strong>Created At:</strong></h6>';
        echo '<p>' . date('M d, Y H:i A', strtotime($template['created_at'])) . '</p>';
        
        if ($template['updated_at']) {
            echo '<h6><strong>Last Updated:</strong></h6>';
            echo '<p>' . date('M d, Y H:i A', strtotime($template['updated_at'])) . '</p>';
        }
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6><strong>Content Preview:</strong></h6>';
        echo '<div class="border p-3" style="max-height: 300px; overflow-y: auto; background-color: #f8f9fa;">';
        echo $template['content'];
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="row mt-3">';
        echo '<div class="col-12">';
        echo '<h6><strong>Raw HTML Content:</strong></h6>';
        echo '<textarea class="form-control" rows="8" readonly>' . htmlspecialchars($template['content']) . '</textarea>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">Template not found</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request</div>';
}
?>