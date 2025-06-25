<?php
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/MailSender.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$userManager = new User();
$mailSender = new MailSender();

// Get user statistics
$stats = [
    'total_emails' => $mailSender->getTotalEmailsByUser($user['id']),
    'sent_emails' => $mailSender->getSentEmailsByUser($user['id']),
    'failed_emails' => $mailSender->getFailedEmailsByUser($user['id'])
];

// Get recent emails
$recent_emails = $mailSender->getEmailsByUser($user['id'], 5, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | My Profile</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        .content-wrapper {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #6f42c1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 3rem;
            margin: 0 auto;
        }
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .activity-item.success {
            border-left-color: #28a745;
        }
        .activity-item.failed {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Main Sidebar Container -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">My Profile</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">My Profile</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Profile Information -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="profile-avatar mb-3">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                                
                                <?php
                                $role_colors = [
                                    'super_admin' => 'danger',
                                    'admin' => 'warning',
                                    'user' => 'info'
                                ];
                                $role_color = $role_colors[$user['role']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $role_color; ?> mb-3">
                                    <?php echo ucwords(str_replace('_', ' ', $user['role'])); ?>
                                </span>
                                
                                <div class="mb-3">
                                    <span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="row text-center">
                                    <div class="col-12">
                                        <a href="settings.php" class="btn btn-primary btn-sm w-100 mb-2">
                                            <i class="fas fa-cog"></i> Account Settings
                                        </a>
                                        <a href="../mail/history.php" class="btn btn-info btn-sm w-100">
                                            <i class="fas fa-envelope"></i> My Email History
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                    </div>
                    
                    <!-- Statistics and Activity -->
                    <div class="col-md-8">
                        <!-- Email Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card stat-card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-envelope fa-2x mb-2"></i>
                                        <h3><?php echo $stats['total_emails']; ?></h3>
                                        <p class="mb-0">Total Emails Sent</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                                        <h3><?php echo $stats['sent_emails']; ?></h3>
                                        <p class="mb-0">Successfully Sent</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                        <h3><?php echo $stats['failed_emails']; ?></h3>
                                        <p class="mb-0">Failed to Send</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Success Rate Chart -->
                        <?php if ($stats['total_emails'] > 0): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-pie mr-1"></i>
                                        Email Success Rate
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $success_rate = ($stats['sent_emails'] / $stats['total_emails']) * 100;
                                    $failure_rate = ($stats['failed_emails'] / $stats['total_emails']) * 100;
                                    ?>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="progress" style="height: 30px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $success_rate; ?>%" title="Success Rate: <?php echo number_format($success_rate, 1); ?>%">
                                                    <?php echo number_format($success_rate, 1); ?>%
                                                </div>
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $failure_rate; ?>%" title="Failure Rate: <?php echo number_format($failure_rate, 1); ?>%">
                                                    <?php echo number_format($failure_rate, 1); ?>%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="text-<?php echo ($success_rate >= 80) ? 'success' : (($success_rate >= 60) ? 'warning' : 'danger'); ?>">
                                                    <?php echo number_format($success_rate, 1); ?>%
                                                </h4>
                                                <small class="text-muted">Success Rate</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Recent Email Activity -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clock mr-1"></i>
                                    Recent Email Activity
                                </h3>
                                <div class="card-tools">
                                    <a href="../mail/history.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-list"></i> View All
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_emails)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No emails sent yet</h5>
                                        <p class="text-muted">Start sending emails to see your activity here.</p>
                                        <a href="../mail/send.php" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Send Your First Email
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_emails as $email): ?>
                                        <div class="activity-item <?php echo ($email['status'] == 'sent') ? 'success' : 'failed'; ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($email['subject']); ?></h6>
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-envelope"></i> To: <?php echo htmlspecialchars($email['to_email']); ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i> <?php echo date('M d, Y H:i A', strtotime($email['sent_at'])); ?>
                                                    </small>
                                                </div>
                                                <div>
                                                    <span class="badge bg-<?php echo ($email['status'] == 'sent') ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($email['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
// Add some interactive effects
$(document).ready(function() {
    // Animate stat cards on hover
    $('.stat-card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
    
    // Add tooltips to progress bars
    $('[title]').tooltip();
});
</script>
</body>
</html>