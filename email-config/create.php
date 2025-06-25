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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $config_name = trim($_POST['config_name']);
    $smtp_host = trim($_POST['smtp_host']);
    $smtp_port = (int)$_POST['smtp_port'];
    $smtp_username = trim($_POST['smtp_username']);
    $smtp_password = $_POST['smtp_password'];
    $encryption = $_POST['encryption'];
    $cc_mail = trim($_POST['cc_mail']);
    $from_name = trim($_POST['from_name']);
    $country = $_POST['country'];
    $category = $_POST['category'];
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
    
    if (empty($smtp_password)) {
        $errors[] = 'SMTP password is required';
    }
    
    if (empty($cc_mail) || !filter_var($cc_mail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid from email is required';
    }
    
    if (empty($from_name)) {
        $errors[] = 'From name is required';
    }
    
    if (empty($country)) {
        $errors[] = 'Country is required';
    }
    
    if (empty($category)) {
        $errors[] = 'Category is required';
    }
    
    if (empty($errors)) {
        $config_id = $emailConfig->createConfig(
            $config_name,
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $encryption,
            $smtp_username,
            $cc_mail,
            $from_name,
            $country,
            $category,
            $status,
            $user['id']
        );
        
        if ($config_id) {
            $success_message = 'Email configuration created successfully';
            // Clear form data
            $_POST = [];
        } else {
            $error_message = 'Error creating email configuration';
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
    <title>Mail Sender | Add Email Configuration</title>
    
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
                        <h1 class="m-0">Add Email Configuration</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Email Configurations</a></li>
                            <li class="breadcrumb-item active">Add Configuration</li>
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
                                                       value="<?php echo isset($_POST['config_name']) ? htmlspecialchars($_POST['config_name']) : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="status">Status *</label>
                                                <select class="form-control" id="status" name="status" required>
                                                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
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
                                                       value="<?php echo isset($_POST['smtp_host']) ? htmlspecialchars($_POST['smtp_host']) : ''; ?>" 
                                                       placeholder="smtp.gmail.com" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="smtp_port">SMTP Port *</label>
                                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                       value="<?php echo isset($_POST['smtp_port']) ? $_POST['smtp_port'] : '587'; ?>" 
                                                       min="1" max="65535" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="smtp_username">SMTP Username *</label>
                                                <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                                       value="<?php echo isset($_POST['smtp_username']) ? htmlspecialchars($_POST['smtp_username']) : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="smtp_password">SMTP Password *</label>
                                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="encryption">Encryption</label>
                                        <select class="form-control" id="encryption" name="encryption">
                                            <option value="tls" <?php echo (isset($_POST['encryption']) && $_POST['encryption'] == 'tls') ? 'selected' : ''; ?>>TLS</option>
                                            <option value="ssl" <?php echo (isset($_POST['encryption']) && $_POST['encryption'] == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                                            <option value="" <?php echo (isset($_POST['encryption']) && $_POST['encryption'] == '') ? 'selected' : ''; ?>>None</option>
                                        </select>
                                    </div>
                                    
                                    <h5 class="mt-4 mb-3">From Settings</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="cc_mail">CC Email *</label>
                                                <input type="email" class="form-control" id="cc_mail" name="cc_mail" 
                                                       value="<?php echo isset($_POST['cc_mail']) ? htmlspecialchars($_POST['cc_mail']) : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="from_name">From Name *</label>
                                                <input type="text" class="form-control" id="from_name" name="from_name" 
                                                       value="<?php echo isset($_POST['from_name']) ? htmlspecialchars($_POST['from_name']) : ''; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="mt-4 mb-3">Additional Settings</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="country">Country *</label>
                                                <input type="text" class="form-control" id="country" name="country" 
                                                       value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="category">Category *</label>
                                                <input type="text" class="form-control" id="category" name="category" 
                                                       value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Configuration
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Common SMTP Settings</h3>
                            </div>
                            <div class="card-body">
                                <h6>Gmail:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Host:</strong> smtp.gmail.com</li>
                                    <li><strong>Port:</strong> 587 (TLS) or 465 (SSL)</li>
                                    <li><strong>Encryption:</strong> TLS or SSL</li>
                                </ul>
                                
                                <h6>Outlook/Hotmail:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Host:</strong> smtp-mail.outlook.com</li>
                                    <li><strong>Port:</strong> 587</li>
                                    <li><strong>Encryption:</strong> TLS</li>
                                </ul>
                                
                                <h6>Yahoo:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Host:</strong> smtp.mail.yahoo.com</li>
                                    <li><strong>Port:</strong> 587 or 465</li>
                                    <li><strong>Encryption:</strong> TLS or SSL</li>
                                </ul>
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
    formData.append('smtp_password', document.getElementById('smtp_password').value);
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