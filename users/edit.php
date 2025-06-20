<?php
require_once '../classes/Auth.php';
require_once '../classes/User.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireRole(['admin', 'super_admin']);

$current_user = $auth->getCurrentUser();
$userManager = new User();

$success_message = '';
$error_message = '';
$user = null;

// Get user ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$user_id = (int)$_GET['id'];
$user = $userManager->getUserById($user_id);

if (!$user) {
    header('Location: index.php');
    exit;
}

// Prevent admin from editing super admin
if ($current_user['role'] == 'admin' && $user['role'] == 'super_admin') {
    $error_message = 'You do not have permission to edit this user';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error_message)) {
    $full_name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? $user['role'];
    $status = $_POST['status'] ?? $user['status'];
    
    // Validation
    if (empty($full_name)) {
        $error_message = 'Name is required';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Valid email address is required';
    } elseif ($userManager->emailExists($email, $user_id)) {
        $error_message = 'Email address already exists';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error_message = 'Passwords do not match';
    } elseif ($current_user['role'] == 'admin' && $role == 'super_admin') {
        $error_message = 'Admin users cannot assign Super Admin role';
    } elseif ($user_id == $current_user['id'] && $role != $current_user['role']) {
        $error_message = 'You cannot change your own role';
    } elseif ($user_id == $current_user['id'] && $status == 'inactive') {
        $error_message = 'You cannot deactivate your own account';
    } else {
        if (!empty($password)) {
            $result = $userManager->updateUser($user_id, $email, $email, $role, $full_name, '', $status);
            if ($result) {
                $userManager->changePassword($user_id, $password);
            }
        } else {
            $result = $userManager->updateUser($user_id, $email, $email, $role, $full_name, '', $status);
        }
        
        if ($result) {
            $success_message = 'User updated successfully';
            // Refresh user data
            $user = $userManager->getUserById($user_id);
        } else {
            $error_message = 'Error updating user';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Edit User</title>

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
        .role-info {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 10px;
            margin-top: 10px;
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #6f42c1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
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
                        <h1 class="m-0">Edit User</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">User Management</a></li>
                            <li class="breadcrumb-item active">Edit User</li>
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
                                    <i class="fas fa-user-edit mr-1"></i>
                                    Edit User: <?php echo htmlspecialchars($user['name']); ?>
                                </h3>
                                <div class="card-tools">
                                    <a href="index.php" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-arrow-left"></i> Back to Users
                                    </a>
                                </div>
                            </div>
                            
                            <!-- User Info Card -->
                            <div class="card-body border-bottom">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                                        <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                                        <p class="text-muted mb-0">
                                            <strong>Created:</strong> <?php echo date('M d, Y H:i A', strtotime($user['created_at'])); ?>
                                            <?php if ($user['last_login']): ?>
                                                | <strong>Last Login:</strong> <?php echo date('M d, Y H:i A', strtotime($user['last_login'])); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="post" action="" id="editUserForm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                                <div class="form-text">Enter the user's full name</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                <div class="form-text">This will be used for login</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password" class="form-label">New Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave empty to keep current password">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                                        <i class="fas fa-eye" id="password-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="password-strength mt-1" id="password-strength"></div>
                                                <div class="form-text">Leave empty to keep current password</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                                        <i class="fas fa-eye" id="confirm_password-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text" id="password-match"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="role" class="form-label">User Role <span class="text-danger">*</span></label>
                                                <select class="form-select" id="role" name="role" onchange="showRoleInfo()" <?php echo ($user_id == $current_user['id']) ? 'disabled' : ''; ?>>
                                                    <?php if ($current_user['role'] == 'super_admin'): ?>
                                                        <option value="super_admin" <?php echo ($user['role'] == 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                                                    <?php endif; ?>
                                                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                    <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                                </select>
                                                <?php if ($user_id == $current_user['id']): ?>
                                                    <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                                                    <div class="form-text text-warning">You cannot change your own role</div>
                                                <?php endif; ?>
                                                <div id="role-info" class="role-info" style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status" <?php echo ($user_id == $current_user['id']) ? 'disabled' : ''; ?>>
                                                    <option value="active" <?php echo ($user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                                <?php if ($user_id == $current_user['id']): ?>
                                                    <input type="hidden" name="status" value="<?php echo $user['status']; ?>">
                                                    <div class="form-text text-warning">You cannot change your own status</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Update User
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="button" class="btn btn-info" onclick="viewUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    User Roles
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6><span class="badge bg-danger">Super Admin</span></h6>
                                    <p class="small text-muted">Full system access including user management, email configurations, and all features.</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><span class="badge bg-warning">Admin</span></h6>
                                    <p class="small text-muted">Can manage users, email configurations, templates, and send emails.</p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><span class="badge bg-info">User</span></h6>
                                    <p class="small text-muted">Can send emails using existing configurations and templates.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-bar mr-1"></i>
                                    User Statistics
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-12 mb-2">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body py-2">
                                                <h5 class="mb-0">ID: <?php echo $user['id']; ?></h5>
                                                <small>User ID</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Created:</strong></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Updated:</strong></td>
                                        <td><?php echo $user['updated_at'] ? date('M d, Y', strtotime($user['updated_at'])) : 'Never'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Login:</strong></td>
                                        <td><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                                    </tr>
                                </table>
                                
                                <a href="../mail/history.php?user_id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm w-100">
                                    <i class="fas fa-envelope"></i> View Email History
                                </a>
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

<!-- User Details Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">
                    <i class="fas fa-user"></i> User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userModalBody">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading user details...</p>
                </div>
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
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + '-eye');
    
    if (field.type === 'password') {
        field.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}

function checkPasswordStrength(password) {
    let strength = 0;
    let color = '';
    let text = '';
    
    if (password.length >= 6) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    switch (strength) {
        case 0:
        case 1:
            color = 'bg-danger';
            text = 'Weak';
            break;
        case 2:
        case 3:
            color = 'bg-warning';
            text = 'Medium';
            break;
        case 4:
        case 5:
            color = 'bg-success';
            text = 'Strong';
            break;
    }
    
    return { strength, color, text };
}

function showRoleInfo() {
    const role = document.getElementById('role').value;
    const roleInfo = document.getElementById('role-info');
    
    let info = '';
    
    switch (role) {
        case 'super_admin':
            info = '<strong>Super Admin:</strong> Full system access including user management, email configurations, templates, and all administrative features.';
            break;
        case 'admin':
            info = '<strong>Admin:</strong> Can manage users, email configurations, templates, and send emails. Cannot create Super Admin accounts.';
            break;
        case 'user':
            info = '<strong>User:</strong> Can send emails using existing configurations and templates. Limited access to administrative features.';
            break;
    }
    
    if (info) {
        roleInfo.innerHTML = info;
        roleInfo.style.display = 'block';
    } else {
        roleInfo.style.display = 'none';
    }
}

function viewUser(userId) {
    $('#userModalBody').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading user details...</p>
        </div>
    `);
    
    $('#userModal').modal('show');
    
    $.ajax({
        url: 'view_user.php',
        method: 'POST',
        data: { user_id: userId },
        success: function(response) {
            $('#userModalBody').html(response);
        },
        error: function() {
            $('#userModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error loading user details
                </div>
            `);
        }
    });
}

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('password-strength');
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthBar.className = 'password-strength';
        return;
    }
    
    const result = checkPasswordStrength(password);
    const percentage = (result.strength / 5) * 100;
    
    strengthBar.style.width = percentage + '%';
    strengthBar.className = 'password-strength ' + result.color;
    strengthBar.title = result.text + ' password';
});

// Password match checker
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const matchDiv = document.getElementById('password-match');
    
    if (confirmPassword.length === 0) {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (password === confirmPassword) {
        matchDiv.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> Passwords match</span>';
    } else {
        matchDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times"></i> Passwords do not match</span>';
    }
});

// Show role info on page load
showRoleInfo();

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>
</body>
</html>