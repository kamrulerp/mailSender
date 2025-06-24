<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailConfig.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole(['admin', 'super_admin']);

$user = $auth->getCurrentUser();
$emailConfig = new EmailConfig();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$configs = $emailConfig->getAllConfigs($limit, $offset);
$total_configs = 0; //$emailConfig->getTotalConfigs();
$total_pages = ceil($total_configs / $limit);

$success_message = '';
$error_message = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $config_id = $_GET['id'];
    if ($emailConfig->deleteConfig($config_id)) {
        $success_message = 'Email configuration deleted successfully';
    } else {
        $error_message = 'Error deleting email configuration';
    }
}

// Handle test action
if (isset($_GET['action']) && $_GET['action'] == 'test' && isset($_GET['id'])) {
    $config_id = $_GET['id'];
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
    <title>Mail Sender | Email Configurations</title>
    
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
                        <h1 class="m-0">Email Configurations</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                            <li class="breadcrumb-item active">Email Configurations</li>
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
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Email Configurations</h3>
                        <div class="card-tools">
                            <a href="create.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add New Configuration
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Configuration Name</th>
                                    <th>SMTP Host</th>
                                    <th>SMTP Port</th>
                                    <th>From Email</th>
                                    <th>From Name</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($configs)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No email configurations found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($configs as $config): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($config['id']); ?></td>
                                    <td><?php echo htmlspecialchars($config['config_name']); ?></td>
                                    <td><?php echo htmlspecialchars($config['smtp_host']); ?></td>
                                    <td><?php echo htmlspecialchars($config['smtp_port']); ?></td>
                                    <td><?php echo htmlspecialchars($config['email_address']); ?></td>
                                    <td><?php echo htmlspecialchars($config['from_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $config['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($config['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($config['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view_config.php?id=<?php echo $config['id']; ?>" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $config['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=test&id=<?php echo $config['id']; ?>" class="btn btn-secondary btn-sm" title="Test Connection">
                                                <i class="fas fa-plug"></i>
                                            </a>
                                            <!-- <button type="button" class="btn btn-danger btn-sm" title="Delete" onclick="confirmDelete(<?php echo $config['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button> -->
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-right">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">&laquo;</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">&raquo;</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
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
    if (confirm('Are you sure you want to delete this email configuration?')) {
        window.location.href = '?action=delete&id=' + configId;
    }
}
</script>

</body>
</html>