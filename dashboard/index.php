<?php
require_once '../classes/Auth.php';
require_once '../classes/User.php';
require_once '../classes/EmailConfig.php';
require_once '../classes/EmailTemplate.php';
require_once '../classes/MailSender.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$userClass = new User();
$emailConfig = new EmailConfig();
$emailTemplate = new EmailTemplate();
$mailSender = new MailSender();

// Get dashboard statistics
$total_users = $userClass->getTotalUsers();
$total_configs = count($emailConfig->getAllConfigs());
$total_templates = $emailTemplate->getTotalTemplates();
$total_emails = $mailSender->getTotalMailHistory();
$recent_emails = $mailSender->getMailHistory(5, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Dashboard</title>

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
        .small-box {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="https://via.placeholder.com/60x60/667eea/ffffff?text=MS" alt="MailSender" height="60" width="60">
    </div>

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
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Small boxes (Stat box) -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $total_emails; ?></h3>
                                <p>Total Emails Sent</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <a href="../mail/history.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $total_templates; ?></h3>
                                <p>Email Templates</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <a href="../templates/index.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $total_configs; ?></h3>
                                <p>Email Configurations</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <a href="../email-config/index.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <?php if ($auth->hasRole(['super_admin', 'admin'])): ?>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $total_users; ?></h3>
                                <p>Total Users</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="../users/index.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Emails -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history mr-1"></i>
                                    Recent Email Activity
                                </h3>
                                <div class="card-tools">
                                    <a href="../mail/history.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> View All
                                    </a>
                                </div>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>To</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Sent At</th>
                                            <th>Sent By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recent_emails)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No emails sent yet</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recent_emails as $email): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($email['to_email']); ?></td>
                                                    <td><?php echo htmlspecialchars(substr($email['subject'], 0, 50)) . (strlen($email['subject']) > 50 ? '...' : ''); ?></td>
                                                    <td>
                                                        <?php if ($email['status'] == 'sent'): ?>
                                                            <span class="badge bg-success">Sent</span>
                                                        <?php elseif ($email['status'] == 'failed'): ?>
                                                            <span class="badge bg-danger">Failed</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($email['sent_at'])); ?></td>
                                                    <td><?php echo htmlspecialchars($email['sent_by_name'] ?? 'Unknown'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-bolt mr-1"></i>
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 col-12">
                                        <a href="../mail/send.php" class="btn btn-app bg-success">
                                            <i class="fas fa-paper-plane"></i> Send Email
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-12">
                                        <a href="../templates/create.php" class="btn btn-app bg-info">
                                            <i class="fas fa-plus"></i> New Template
                                        </a>
                                    </div>
                                    <?php if ($auth->hasRole(['super_admin', 'admin'])): ?>
                                    <div class="col-md-3 col-sm-6 col-12">
                                        <a href="../email-config/create.php" class="btn btn-app bg-warning">
                                            <i class="fas fa-cog"></i> Add Config
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-12">
                                        <a href="../users/create.php" class="btn btn-app bg-danger">
                                            <i class="fas fa-user-plus"></i> Add User
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
    </aside>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>