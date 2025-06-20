<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailTemplate.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$emailTemplate = new EmailTemplate();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($title)) {
        $error_message = 'Template title is required';
    } elseif (empty($content)) {
        $error_message = 'Template content is required';
    } else {
        $template_id = $emailTemplate->createTemplate($title, $content, $status, $user['id']);
        
        if ($template_id) {
            $success_message = 'Template created successfully';
            // Clear form data
            $title = '';
            $content = '';
            $status = 'active';
        } else {
            $error_message = 'Error creating template';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Create Email Template</title>

     <!-- Google Font: Source Sans Pro -->
     <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Summernote -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
    
    <style>
        .content-wrapper {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .note-editor {
            border-radius: 10px;
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
                        <h1 class="m-0">Create Email Template</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Email Templates</a></li>
                            <li class="breadcrumb-item active">Create Template</li>
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
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus mr-1"></i>
                                    Create New Email Template
                                </h3>
                                <div class="card-tools">
                                    <a href="index.php" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-arrow-left"></i> Back to Templates
                                    </a>
                                </div>
                            </div>
                            <form method="post" action="">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Template Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                                                <div class="form-text">Enter a descriptive title for your email template</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="active" <?php echo (isset($status) && $status == 'active') ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo (isset($status) && $status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Template Content <span class="text-danger">*</span></label>
                                        <textarea id="content" name="content" class="form-control summernote"><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                                        <div class="form-text">Use the rich text editor to create your email template. You can include HTML formatting, images, and links.</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Template Variables</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p class="card-text">You can use these variables in your template:</p>
                                                    <ul class="list-unstyled">
                                                        <li><code>{{recipient_name}}</code> - Recipient's name</li>
                                                        <li><code>{{recipient_email}}</code> - Recipient's email</li>
                                                        <li><code>{{sender_name}}</code> - Sender's name</li>
                                                        <li><code>{{sender_designation}}</code> - Sender's designation</li>
                                                        <li><code>{{current_date}}</code> - Current date</li>
                                                        <li><code>{{current_time}}</code> - Current time</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Create Template
                                    </button>
                                   
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
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
<!-- Image Upload -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Summernote
    $('#content').summernote({
        height: 400,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        placeholder: 'Enter your email template content here...',
        callbacks: {
            onImageUpload: function(files) {
                // Handle image upload if needed
                for (let i = 0; i < files.length; i++) {
                    uploadImage(files[i]);
                }
            }
        }
    });
});




function uploadImage(file) {
    // Simple base64 image insertion
    const reader = new FileReader();
    reader.onload = function(e) {
        $('#content').summernote('insertImage', e.target.result);
    };
    reader.readAsDataURL(file);
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>
</body>
</html>