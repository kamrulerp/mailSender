<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailConfig.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole(['admin', 'super_admin']);

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['config_id'])) {
    $config_id = (int)$_POST['config_id'];
    $emailConfig = new EmailConfig();
    
    $config = $emailConfig->getConfigById($config_id);
    
    if ($config) {
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6><strong>Configuration Name:</strong></h6>';
        echo '<p>' . htmlspecialchars($config['name']) . '</p>';
        
        echo '<h6><strong>Email Address:</strong></h6>';
        echo '<p class="config-email">' . htmlspecialchars($config['email']) . '</p>';
        
        echo '<h6><strong>From Name:</strong></h6>';
        echo '<p>' . htmlspecialchars($config['from_name']) . '</p>';
        
        echo '<h6><strong>Status:</strong></h6>';
        if ($config['status'] == 'active') {
            echo '<span class="badge bg-success">Active</span>';
        } else {
            echo '<span class="badge bg-secondary">Inactive</span>';
        }
        
        echo '<h6 class="mt-3"><strong>Created At:</strong></h6>';
        echo '<p>' . date('M d, Y H:i A', strtotime($config['created_at'])) . '</p>';
        
        if ($config['updated_at']) {
            echo '<h6><strong>Last Updated:</strong></h6>';
            echo '<p>' . date('M d, Y H:i A', strtotime($config['updated_at'])) . '</p>';
        }
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6><strong>SMTP Configuration:</strong></h6>';
        echo '<table class="table table-sm table-bordered">';
        echo '<tr><td><strong>Host:</strong></td><td>' . htmlspecialchars($config['smtp_host']) . '</td></tr>';
        echo '<tr><td><strong>Port:</strong></td><td>' . $config['smtp_port'] . '</td></tr>';
        echo '<tr><td><strong>Username:</strong></td><td>' . htmlspecialchars($config['smtp_username']) . '</td></tr>';
        echo '<tr><td><strong>Password:</strong></td><td>••••••••</td></tr>';
        echo '<tr><td><strong>Encryption:</strong></td><td>';
        if ($config['smtp_encryption']) {
            echo '<span class="badge bg-info">' . strtoupper($config['smtp_encryption']) . '</span>';
        } else {
            echo '<span class="badge bg-secondary">None</span>';
        }
        echo '</td></tr>';
        echo '</table>';
        
        echo '<div class="mt-3">';
        echo '<a href="?action=test&id=' . $config['id'] . '" class="btn btn-primary btn-sm" onclick="return confirm(\'Test SMTP connection for this configuration?\')">';
        echo '<i class="fas fa-plug"></i> Test Connection';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        if ($config['description']) {
            echo '<div class="row mt-3">';
            echo '<div class="col-12">';
            echo '<h6><strong>Description:</strong></h6>';
            echo '<p>' . nl2br(htmlspecialchars($config['description'])) . '</p>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Configuration not found</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request</div>';
}
?>

<style>
.config-email {
    font-family: monospace;
    background-color: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
</style>