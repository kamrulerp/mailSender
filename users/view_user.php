<?php
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/MailSender.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole(['admin', 'super_admin']);

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['user_id'])) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit;
}

$user_id = (int)$_POST['user_id'];
$userManager = new User();
$mailSender = new MailSender();

$user = $userManager->getUserById($user_id);
if (!$user) {
    echo '<div class="alert alert-danger">User not found</div>';
    exit;
}

// Get user statistics
$stats = [
    'total_emails' => $mailSender->getTotalEmailsByUser($user_id),
    'sent_emails' => $mailSender->getSentEmailsByUser($user_id),
    'failed_emails' => $mailSender->getFailedEmailsByUser($user_id)
];
?>

<div class="row">
    <div class="col-md-4">
        <div class="text-center">
            <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; background: linear-gradient(45deg, #007bff, #6f42c1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <h5><?php echo htmlspecialchars($user['username']); ?></h5>
            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
            
            <?php
            $role_colors = [
                'super_admin' => 'danger',
                'admin' => 'warning', 
                'user' => 'info'
            ];
            $role_color = $role_colors[$user['role']] ?? 'secondary';
            ?>
            <span class="badge bg-<?php echo $role_color; ?> mb-2">
                <?php echo ucwords(str_replace('_', ' ', $user['role'])); ?>
            </span>
            
            <div class="mb-3">
                <span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'secondary'; ?>">
                    <?php echo ucfirst($user['status']); ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>User ID:</strong><br>
                <span class="text-muted"><?php echo $user['id']; ?></span>
            </div>
            <div class="col-md-6">
                <strong>Created:</strong><br>
                <span class="text-muted"><?php echo date('M d, Y H:i A', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
        
        
        
        <hr>
        
        <h6><i class="fas fa-chart-bar"></i> Email Statistics</h6>
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $stats['total_emails']; ?></h4>
                        <small>Total Emails</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $stats['sent_emails']; ?></h4>
                        <small>Sent Successfully</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $stats['failed_emails']; ?></h4>
                        <small>Failed</small>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($stats['total_emails'] > 0): ?>
            <div class="mt-3">
                <div class="progress">
                    <?php 
                    $success_rate = ($stats['sent_emails'] / $stats['total_emails']) * 100;
                    $failure_rate = ($stats['failed_emails'] / $stats['total_emails']) * 100;
                    ?>
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $success_rate; ?>%" title="Success Rate: <?php echo number_format($success_rate, 1); ?>%"></div>
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $failure_rate; ?>%" title="Failure Rate: <?php echo number_format($failure_rate, 1); ?>%"></div>
                </div>
                <small class="text-muted">
                    Success Rate: <?php echo number_format($success_rate, 1); ?>% | 
                    Failure Rate: <?php echo number_format($failure_rate, 1); ?>%
                </small>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4">
    <div class="d-flex justify-content-between">
        <div>
            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <?php if ($user['id'] != $auth->getCurrentUser()['id']): ?>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                    <i class="fas fa-trash"></i> Delete User
                </button>
            <?php endif; ?>
        </div>
        <div>
            <a href="../mail/history.php?user_id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">
                <i class="fas fa-envelope"></i> View Email History
            </a>
        </div>
    </div>
</div>