<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailConfig.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole(['admin', 'super_admin']);

$user = $auth->getCurrentUser();
$emailConfig = new EmailConfig();

$success_message = '';
$error_message = '';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $config_name = trim($_POST['config_name']);
    $smtp_host = trim($_POST['smtp_host']);
    $smtp_port = (int)$_POST['smtp_port'];
    $smtp_username = trim($_POST['smtp_username']);
    $smtp_password = $_POST['smtp_password'];
    $encryption = $_POST['encryption'];
    $from_email = trim($_POST['from_email']);
    $from_name = trim($_POST['from_name']);
    $status = $_POST['status'];
    
    // Validation
    $errors = [];
    
    if (empty($config_name)) {
        $errors[] = 'Configuration name is required';
    }
    
    if (empty($smtp_host)) {
        $errors[] = 'SMTP host is required';
    }
    
    if (empty($smtp_port) || $smtp_port < 1 || $smtp_port > 65535) {
        $errors[] = 'Valid SMTP port is required (1-65535)';
    }
    
    if (empty($smtp_username)) {
        $errors[] = 'SMTP username is required';
    }
    
    if (empty($from_email) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid from email is required';
    }
    
    if (empty($from_name)) {
        $errors[] = 'From name is required';
    }
    
    if (empty($errors)) {
        // If password is empty, keep the existing password
        if (empty($smtp_password)) {
            $smtp_password = $config['smtp_password'];
        }
        
        $result = $emailConfig->updateConfig(
            $config_id,
            $config_name,
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $encryption,
            $from_email,
            $from_name,
            $status
        );
        
        if ($result) {
            $success_message = 'Email configuration updated successfully';
            // Refresh config data
            $config = $emailConfig->getConfigById($config_id);
        } else {
            $error_message = 'Error updating email configuration';
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Edit Email Configuration</title>
    
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
                        <h1 class="m-0">Edit Email Configuration</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Email Configurations</a></li>
                            <li class="breadcrumb-item active">Edit Configuration</li>
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
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Configuration Details</h3>
                            </div>
                            
                            <form method="POST">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="config_name">Configuration Name *</label>
                                                <input type="text" class="form-control" id="config_name" name="config_name" 
                                                       value="<?php echo isset($_POST['config_name']) ? htmlspecialchars($_POST['config_name']) : htmlspecialchars($config['config_name']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="status">Status *</label>
                                                <select class="form-control" id="status" name="status" required>
                                                    <?php $current_status = isset($_POST['status']) ? $_POST['status'] : $config['status']; ?>
                                                    <option value="active" <?php echo $current_status == 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $current_status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h5 class="mt-4 mb-3">SMTP Settings</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="smtp_host">SMTP Host *</label>
                                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                                       value="<?php echo isset($_POST['smtp_host']) ? htmlspecialchars($_POST['smtp_host']) : htmlspecialchars($config['smtp_host']); ?>" 
                                                       placeholder="smtp.gmail.com" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="smtp_port">SMTP Port *</label>
                                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                       value="<?php echo isset($_POST['smtp_port']) ? $_POST['smtp_port'] : $config['smtp_port']; ?>" 
                                                       min="1" max="65535" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="smtp_username">SMTP Username *</label>
                                                <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                                       value="<?php echo isset($_POST['smtp_username']) ? htmlspecialchars($_POST['smtp_username']) : htmlspecialchars($config['smtp_username']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="smtp_password">SMTP Password</label>
                                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                                       placeholder="Leave empty to keep current password">
                                                <small class="form-text text-muted">Leave empty to keep the current password</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="encryption">Encryption</label>
                                        <select class="form-control" id="encryption" name="encryption">
                                            <?php $current_encryption = isset($_POST['encryption']) ? $_POST['encryption'] : $config['encryption']; ?>
                                            <option value="tls" <?php echo $current_encryption == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                            <option value="ssl" <?php echo $current_encryption == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                            <option value="" <?php echo $current_encryption == '' ? 'selected' : ''; ?>>None</option>
                                        </select>
                                    </div>
                                    
                                    <h5 class="mt-4 mb-3">From Settings</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="from_email">From Email *</label>
                                                <input type="email" class="form-control" id="from_email" name="from_email" 
                                                       value="<?php echo isset($_POST['from_email']) ? htmlspecialchars($_POST['from_email']) : htmlspecialchars($config['from_email']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="from_name">From Name *</label>
                                                <input type="text" class="form-control" id="from_name" name="from_name" 
                                                       value="<?php echo isset($_POST['from_name']) ? htmlspecialchars($_POST['from_name']) : htmlspecialchars($config['from_name']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Configuration
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <a href="view_config.php?id=<?php echo $config['id']; ?>" class="btn btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button type="button" class="btn btn-warning" onclick="testConnection()">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Configuration Info</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>ID:</strong> <?php echo $config['id']; ?></p>
                                <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($config['created_at'])); ?></p>
                                <p><strong>Updated:</strong> <?php echo date('M d, Y H:i', strtotime($config['updated_at'])); ?></p>
                                <p><strong>Created By:</strong> <?php echo htmlspecialchars($config['created_by']); ?></p>
                            </div>
                        </div>
                        
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Security Note</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    For security reasons, the current password is not displayed. 
                                    Leave the password field empty to keep the existing password.
                                </p>
                            </div>
                        </div>
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
function testConnection() {
    const formData = new FormData();
    formData.append('smtp_host', document.getElementById('smtp_host').value);
    formData.append('smtp_port', document.getElementById('smtp_port').value);
    formData.append('smtp_username', document.getElementById('smtp_username').value);
    
    // For testing, we need the password. If empty, ask user to enter it
    const password = document.getElementById('smtp_password').value;
    if (!password) {
        const testPassword = prompt('Enter SMTP password for testing (current password will be used if you leave this empty):');
        if (testPassword) {
            formData.append('smtp_password', testPassword);
        } else {
            alert('Password is required for testing connection');
            return;
        }
    } else {
        formData.append('smtp_password', password);
    }
    
    formData.append('encryption', document.getElementById('encryption').value);
    formData.append('from_email', document.getElementById('from_email').value);
    formData.append('from_name', document.getElementById('from_name').value);
    
    fetch('../config/test_connection.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('SMTP connection test successful!');
        } else {
            alert('SMTP connection test failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error testing connection: ' + error.message);
    });
}
</script>

</body>
</html>