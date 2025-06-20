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
$total_configs = $emailConfig->getTotalConfigs();
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
        .config-email {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
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
                        <h1 class="m-0">Email Configurations</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
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
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs mr-1"></i>
                            Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <a href="create.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Add New Configuration
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configurations Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-server mr-1"></i>
                            Email Configurations (<?php echo $total_configs; ?> total)
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>SMTP Host</th>
                                    <th>Port</th>
                                    <th>Encryption</th>
                                    <th>Status</th>
                                    <th>Created At</th>
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
                                            <td><?php echo $config['id']; ?></td>
                                            <td><?php echo htmlspecialchars($config['config_name']); ?></td>
                                            <td><span class="config-email"><?php echo htmlspecialchars($config['email']); ?></span></td>
                                            <td><?php echo htmlspecialchars($config['smtp_host']); ?></td>
                                            <td><?php echo $config['smtp_port']; ?></td>
                                            <td>
                                                <?php if ($config['smtp_encryption']): ?>
                                                    <span class="badge bg-info"><?php echo strtoupper($config['smtp_encryption']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($config['status'] == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($config['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#configModal" onclick="viewConfig(<?php echo $config['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="edit.php?id=<?php echo $config['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=test&id=<?php echo $config['id']; ?>" class="btn btn-primary btn-sm" onclick="return confirm('Test SMTP connection for this configuration?')">
                                                        <i class="fas fa-plug"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteConfig(<?php echo $config['id']; ?>, '<?php echo htmlspecialchars($config['config_name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</div>

<!-- Configuration View Modal -->
<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configModalLabel">Configuration Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="configModalBody">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
function viewConfig(configId) {
    $('#configModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');
    
    $.ajax({
        url: 'view_config.php',
        method: 'POST',
        data: { config_id: configId },
        success: function(response) {
            $('#configModalBody').html(response);
        },
        error: function() {
            $('#configModalBody').html('<div class="alert alert-danger">Error loading configuration</div>');
        }
    });
}

function deleteConfig(configId, configName) {
    if (confirm('Are you sure you want to delete the configuration "' + configName + '"?\n\nThis action cannot be undone.')) {
        window.location.href = 'index.php?action=delete&id=' + configId;
    }
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>
</body>
</html>