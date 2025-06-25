<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailConfig.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole(['admin', 'super_admin']);

$user = $auth->getCurrentUser();
$emailConfig = new EmailConfig();

// Get configuration ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$config_id = (int)$_GET['id'];
$config = $emailConfig->getConfigById($config_id);

if (!$config) {
    header('Location: index.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle test connection
if (isset($_GET['action']) && $_GET['action'] == 'test') {
    $test_result = $emailConfig->testConnection($config_id);
    if ($test_result['success']) {
        $success_message = 'SMTP connection test successful';
    } else {
        $error_message = 'SMTP connection test failed: ' . $test_result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | View Email Configuration</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">View Email Configuration</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Email Configurations</a></li>
                            <li class="breadcrumb-item active">View Configuration</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                
                <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-cog"></i> 
                                    <?php echo htmlspecialchars($config['config_name']); ?>
                                </h3>
                                <div class="card-tools">
                                    <span class="badge <?php echo $config['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($config['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Basic Information</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Configuration ID:</strong></td>
                                                <td><?php echo $config['id']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Configuration Name:</strong></td>
                                                <td><?php echo htmlspecialchars($config['config_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>
                                                    <span class="badge <?php echo $config['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo ucfirst($config['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Created:</strong></td>
                                                <td><?php echo date('M d, Y H:i:s', strtotime($config['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Last Updated:</strong></td>
                                                <td><?php echo date('M d, Y H:i:s', strtotime($config['updated_at'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5>SMTP Settings</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>SMTP Host:</strong></td>
                                                <td><?php echo htmlspecialchars($config['smtp_host']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>SMTP Port:</strong></td>
                                                <td><?php echo $config['smtp_port']; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>SMTP Username:</strong></td>
                                                <td><?php echo htmlspecialchars($config['smtp_username']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>SMTP Password:</strong></td>
                                                <td>
                                                    <span class="text-muted">
                                                        <i class="fas fa-lock"></i> Hidden for security
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Encryption:</strong></td>
                                                <td>
                                                    <?php if ($config['encryption']): ?>
                                                        <span class="badge bg-info"><?php echo strtoupper($config['encryption']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>From Settings</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td style="width: 150px;"><strong>From Email:</strong></td>
                                                <td><?php echo htmlspecialchars($config['from_email']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>From Name:</strong></td>
                                                <td><?php echo htmlspecialchars($config['from_name']); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div class="btn-group" role="group">
                                    <a href="edit.php?id=<?php echo $config['id']; ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit Configuration
                                    </a>
                                    <a href="?id=<?php echo $config['id']; ?>&action=test" class="btn btn-info">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $config['id']; ?>)">
                                        <i class="fas fa-trash"></i> Delete Configuration
                                    </button>
                                </div>
                                <a href="index.php" class="btn btn-secondary float-right">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="../mail/send.php?config_id=<?php echo $config['id']; ?>" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Send Email with this Config
                                    </a>
                                    <a href="create.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create New Configuration
                                    </a>
                                    <a href="../mail/history.php?config_id=<?php echo $config['id']; ?>" class="btn btn-secondary">
                                        <i class="fas fa-history"></i> View Email History
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Connection Status</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Click "Test Connection" to verify if this SMTP configuration is working properly.
                                </p>
                                <div class="text-center">
                                    <a href="?id=<?php echo $config['id']; ?>&action=test" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-plug"></i> Test Now
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($config['status'] == 'inactive'): ?>
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Configuration Inactive</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    This configuration is currently inactive and cannot be used for sending emails.
                                </p>
                                <div class="text-center">
                                    <a href="edit.php?id=<?php echo $config['id']; ?>" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit"></i> Activate Configuration
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
function confirmDelete(configId) {
    if (confirm('Are you sure you want to delete this email configuration? This action cannot be undone.')) {
        window.location.href = 'index.php?action=delete&id=' + configId;
    }
}
</script>

</body>
</html>