<?php
require_once '../classes/Auth.php';
require_once '../classes/User.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$userManager = new User();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) {
        $error_message = 'Name is required.';
    } elseif (empty($email)) {
        $error_message = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if email already exists (excluding current user)
        if ($email != $user['email']) {
            $error_message = 'Email address already exists.';
        } else {
            $password_hash = null;
            
            // If password change is requested
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error_message = 'Current password is required to change password.';
                } elseif (!password_verify($current_password, $user['password'])) {
                    $error_message = 'Current password is incorrect.';
                } elseif (strlen($new_password) < 6) {
                    $error_message = 'New password must be at least 6 characters long.';
                } elseif ($new_password !== $confirm_password) {
                    $error_message = 'New password and confirm password do not match.';
                } else {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }
            
            if (empty($error_message)) {
                if ($userManager->updateProfile($user['id'], $name, $email, $password_hash)) {
                    $success_message = 'Profile updated successfully!';
                    // Refresh user data
                    $user = $userManager->getUserById($user['id']);
                } else {
                    $error_message = 'Failed to update profile. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Account Settings</title>

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
        .password-strength {
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        .password-strength.weak {
            background-color: #dc3545;
            width: 33%;
        }
        .password-strength.medium {
            background-color: #ffc107;
            width: 66%;
        }
        .password-strength.strong {
            background-color: #28a745;
            width: 100%;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-toggle-password {
            border: none;
            background: none;
            color: #6c757d;
        }
        .btn-toggle-password:hover {
            color: #007bff;
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
                        <h1 class="m-0">Account Settings</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">My Profile</a></li>
                            <li class="breadcrumb-item active">Settings</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-cog mr-2"></i>
                                    Update Profile Information
                                </h3>
                            </div>
                            <form method="POST" id="settingsForm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Role</label>
                                                <input type="text" class="form-control" value="<?php echo ucwords(str_replace('_', ' ', $user['role'])); ?>" readonly>
                                                <small class="form-text text-muted">Role cannot be changed from this page.</small>
                                            </div>
                                        </div>
                                       
                                    </div>
                                    
                                    <hr>
                                    
                                    <h5 class="mb-3">
                                        <i class="fas fa-lock mr-2"></i>
                                        Change Password
                                        <small class="text-muted">(Leave blank to keep current password)</small>
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Current Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                                    <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="current_password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <small class="form-text text-muted">Required only if you want to change your password.</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">New Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                                    <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="new_password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="mt-2">
                                                    <div class="password-strength" id="passwordStrength"></div>
                                                    <small id="passwordStrengthText" class="form-text text-muted">Password strength will appear here</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                                    <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="confirm_password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                                <small id="passwordMatch" class="form-text"></small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Password Requirements:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>At least 6 characters long</li>
                                            <li>Mix of uppercase and lowercase letters (recommended)</li>
                                            <li>Include numbers and special characters (recommended)</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Profile
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Profile
                                        </button>
                                    </div>
                                </div>
                            </form>
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
$(document).ready(function() {
    // Toggle password visibility
    $('.btn-toggle-password').click(function() {
        const target = $(this).data('target');
        const input = $('#' + target);
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Password strength checker
    $('#new_password').on('input', function() {
        const password = $(this).val();
        const strengthBar = $('#passwordStrength');
        const strengthText = $('#passwordStrengthText');
        
        if (password.length === 0) {
            strengthBar.removeClass('weak medium strong').css('width', '0%');
            strengthText.text('Password strength will appear here').removeClass('text-danger text-warning text-success');
            return;
        }
        
        let strength = 0;
        let feedback = [];
        
        // Length check
        if (password.length >= 6) strength++;
        else feedback.push('at least 6 characters');
        
        // Uppercase check
        if (/[A-Z]/.test(password)) strength++;
        else feedback.push('uppercase letter');
        
        // Lowercase check
        if (/[a-z]/.test(password)) strength++;
        else feedback.push('lowercase letter');
        
        // Number check
        if (/\d/.test(password)) strength++;
        else feedback.push('number');
        
        // Special character check
        if (/[^\w\s]/.test(password)) strength++;
        else feedback.push('special character');
        
        // Update strength indicator
        strengthBar.removeClass('weak medium strong');
        
        if (strength <= 2) {
            strengthBar.addClass('weak');
            strengthText.text('Weak - Add: ' + feedback.slice(0, 2).join(', ')).removeClass('text-warning text-success').addClass('text-danger');
        } else if (strength <= 3) {
            strengthBar.addClass('medium');
            strengthText.text('Medium - Add: ' + feedback.slice(0, 1).join(', ')).removeClass('text-danger text-success').addClass('text-warning');
        } else {
            strengthBar.addClass('strong');
            strengthText.text('Strong password!').removeClass('text-danger text-warning').addClass('text-success');
        }
    });
    
    // Password match checker
    $('#confirm_password').on('input', function() {
        const password = $('#new_password').val();
        const confirmPassword = $(this).val();
        const matchText = $('#passwordMatch');
        
        if (confirmPassword.length === 0) {
            matchText.text('').removeClass('text-success text-danger');
            return;
        }
        
        if (password === confirmPassword) {
            matchText.text('Passwords match!').removeClass('text-danger').addClass('text-success');
        } else {
            matchText.text('Passwords do not match').removeClass('text-success').addClass('text-danger');
        }
    });
    
    // Form validation
    $('#settingsForm').on('submit', function(e) {
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        const currentPassword = $('#current_password').val();
        
        if (newPassword && !currentPassword) {
            e.preventDefault();
            alert('Current password is required to change password.');
            $('#current_password').focus();
            return false;
        }
        
        if (newPassword && newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New password and confirm password do not match.');
            $('#confirm_password').focus();
            return false;
        }
        
        if (newPassword && newPassword.length < 6) {
            e.preventDefault();
            alert('New password must be at least 6 characters long.');
            $('#new_password').focus();
            return false;
        }
    });
});
</script>
</body>
</html>