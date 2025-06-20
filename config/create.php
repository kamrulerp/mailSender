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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $config_name = trim($_POST['config_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $from_name = trim($_POST['from_name'] ?? '');
    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = (int)($_POST['smtp_port'] ?? 587);
    $smtp_username = trim($_POST['smtp_username'] ?? '');
    $smtp_password = $_POST['smtp_password'] ?? '';
    $smtp_encryption = $_POST['smtp_encryption'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($config_name)) {
        $error_message = 'Configuration name is required';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Valid email address is required';
    } elseif (empty($from_name)) {
        $error_message = 'From name is required';
    } elseif (empty($smtp_host)) {
        $error_message = 'SMTP host is required';
    } elseif (empty($smtp_username)) {
        $error_message = 'SMTP username is required';
    } elseif (empty($smtp_password)) {
        $error_message = 'SMTP password is required';
    } else {
        $config_id = $emailConfig->createConfig(
            $config_name,
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $smtp_encryption,
            $email,
            $from_name,
            $status,
            $user['id']
        );
        
        if ($config_id) {
            $success_message = 'Email configuration created successfully';
            // Clear form data
            $config_name = $email = $from_name = $smtp_host = $smtp_username = $smtp_password = $description = '';
            $smtp_port = 587;
            $smtp_encryption = '';
            $status = 'active';
        } else {
            $error_message = 'Error creating email configuration';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Create Email Configuration</title>

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
        .smtp-preset {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .smtp-preset:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
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
                        <h1 class="m-0">Create Email Configuration</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Email Configurations</a></li>
                            <li class="breadcrumb-item active">Create Configuration</li>
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

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus mr-1"></i>
                                    Create New Email Configuration
                                </h3>
                                <div class="card-tools">
                                    <a href="index.php" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-arrow-left"></i> Back to Configurations
                                    </a>
                                </div>
                            </div>
                            <form method="post" action="">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="config_name" class="form-label">Configuration Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="config_name" name="config_name" value="<?php echo htmlspecialchars($config_name ?? ''); ?>" required>
                                                <div class="form-text">Enter a descriptive name for this configuration</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="active" <?php echo (isset($status) && $status == 'active') ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo (isset($status) && $status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                                <div class="form-text">The email address to send from</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="from_name" class="form-label">From Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="from_name" name="from_name" value="<?php echo htmlspecialchars($from_name ?? ''); ?>" required>
                                                <div class="form-text">The name that will appear as sender</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    <h5><i class="fas fa-server"></i> SMTP Configuration</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="smtp_host" class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($smtp_host ?? ''); ?>" required>
                                                <div class="form-text">SMTP server hostname</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="smtp_port" class="form-label">SMTP Port <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo $smtp_port ?? 587; ?>" required>
                                                <div class="form-text">Usually 587 or 465</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="smtp_encryption" class="form-label">Encryption</label>
                                                <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                                    <option value="" <?php echo (isset($smtp_encryption) && $smtp_encryption == '') ? 'selected' : ''; ?>>None</option>
                                                    <option value="tls" <?php echo (isset($smtp_encryption) && $smtp_encryption == 'tls') ? 'selected' : ''; ?>>TLS</option>
                                                    <option value="ssl" <?php echo (isset($smtp_encryption) && $smtp_encryption == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="smtp_username" class="form-label">SMTP Username <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($smtp_username ?? ''); ?>" required>
                                                <div class="form-text">Usually the same as email address</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="smtp_password" class="form-label">SMTP Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" required>
                                                <div class="form-text">SMTP authentication password</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                                        <div class="form-text">Optional description for this configuration</div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Create Configuration
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="testConnection()">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-magic mr-1"></i>
                                    Quick Setup
                                </h3>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Click on a provider to auto-fill SMTP settings:</p>
                                
                                <div class="smtp-preset border rounded p-2 mb-2" onclick="setGmailConfig()">
                                    <strong><i class="fab fa-google"></i> Gmail</strong><br>
                                    <small class="text-muted">smtp.gmail.com:587 (TLS)</small>
                                </div>
                                
                                <div class="smtp-preset border rounded p-2 mb-2" onclick="setOutlookConfig()">
                                    <strong><i class="fab fa-microsoft"></i> Outlook/Hotmail</strong><br>
                                    <small class="text-muted">smtp-mail.outlook.com:587 (TLS)</small>
                                </div>
                                
                                <div class="smtp-preset border rounded p-2 mb-2" onclick="setYahooConfig()">
                                    <strong><i class="fab fa-yahoo"></i> Yahoo Mail</strong><br>
                                    <small class="text-muted">smtp.mail.yahoo.com:587 (TLS)</small>
                                </div>
                                
                                <div class="smtp-preset border rounded p-2 mb-2" onclick="setMailgunConfig()">
                                    <strong><i class="fas fa-envelope"></i> Mailgun</strong><br>
                                    <small class="text-muted">smtp.mailgun.org:587 (TLS)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Help & Tips
                                </h3>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Use app-specific passwords for Gmail</li>
                                    <li><i class="fas fa-check text-success"></i> Enable "Less secure app access" if needed</li>
                                    <li><i class="fas fa-check text-success"></i> Test connection before saving</li>
                                    <li><i class="fas fa-check text-success"></i> Use TLS encryption when possible</li>
                                </ul>
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
function setGmailConfig() {
    $('#smtp_host').val('smtp.gmail.com');
    $('#smtp_port').val(587);
    $('#smtp_encryption').val('tls');
}

function setOutlookConfig() {
    $('#smtp_host').val('smtp-mail.outlook.com');
    $('#smtp_port').val(587);
    $('#smtp_encryption').val('tls');
}

function setYahooConfig() {
    $('#smtp_host').val('smtp.mail.yahoo.com');
    $('#smtp_port').val(587);
    $('#smtp_encryption').val('tls');
}

function setMailgunConfig() {
    $('#smtp_host').val('smtp.mailgun.org');
    $('#smtp_port').val(587);
    $('#smtp_encryption').val('tls');
}

function testConnection() {
    const formData = {
        smtp_host: $('#smtp_host').val(),
        smtp_port: $('#smtp_port').val(),
        smtp_username: $('#smtp_username').val(),
        smtp_password: $('#smtp_password').val(),
        smtp_encryption: $('#smtp_encryption').val()
    };
    
    // Basic validation
    if (!formData.smtp_host || !formData.smtp_username || !formData.smtp_password) {
        alert('Please fill in all SMTP fields before testing.');
        return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    btn.disabled = true;
    
    $.ajax({
        url: 'test_connection.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ SMTP connection test successful!');
            } else {
                alert('❌ SMTP connection test failed: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Error testing SMTP connection');
        },
        complete: function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>
</body>
</html>