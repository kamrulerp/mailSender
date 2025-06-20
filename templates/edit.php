<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailTemplate.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$emailTemplate = new EmailTemplate();

$success_message = '';
$error_message = '';
$template = null;

// Get template ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$template_id = (int)$_GET['id'];
$template = $emailTemplate->getTemplateById($template_id);

if (!$template) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($title)) {
        $error_message = 'Template title is required';
    } elseif (empty($content)) {
        $error_message = 'Template content is required';
    } else {
        $result = $emailTemplate->updateTemplate($template_id, $title, $content, $status);
        
        if ($result) {
            $success_message = 'Template updated successfully';
            // Refresh template data
            $template = $emailTemplate->getTemplateById($template_id);
        } else {
            $error_message = 'Error updating template';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Edit Email Template</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.css" rel="stylesheet">
    
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
                        <h1 class="m-0">Edit Email Template</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Email Templates</a></li>
                            <li class="breadcrumb-item active">Edit Template</li>
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
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit Email Template: <?php echo htmlspecialchars($template['title']); ?>
                                </h3>
                                <div class="card-tools">
                                    <a href="index.php" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-arrow-left"></i> Back to Templates
                                    </a>
                                </div>
                            </div>
                            <form method="post" action="">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Template Information</h6>
                                                    <p class="card-text">
                                                        <strong>Created by:</strong> <?php echo htmlspecialchars($template['created_by_name'] ?? 'Unknown'); ?><br>
                                                        <strong>Created at:</strong> <?php echo date('M d, Y H:i A', strtotime($template['created_at'])); ?><br>
                                                        <?php if ($template['updated_at']): ?>
                                                            <strong>Last updated:</strong> <?php echo date('M d, Y H:i A', strtotime($template['updated_at'])); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Quick Actions</h6>
                                                    <button type="button" class="btn btn-info btn-sm" onclick="previewTemplate()">
                                                        <i class="fas fa-eye"></i> Preview
                                                    </button>
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="duplicateTemplate(<?php echo $template['id']; ?>)">
                                                        <i class="fas fa-copy"></i> Duplicate
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Template Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($template['title']); ?>" required>
                                                <div class="form-text">Enter a descriptive title for your email template</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="active" <?php echo ($template['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo ($template['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Template Content <span class="text-danger">*</span></label>
                                        <textarea id="content" name="content" class="form-control"><?php echo htmlspecialchars($template['content']); ?></textarea>
                                        <div class="form-text">Use the rich text editor to modify your email template. You can include HTML formatting, images, and links.</div>
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
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Insert Elements</h5>
                                                </div>
                                                <div class="card-body">
                                                    <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="insertVariable('{{recipient_name}}')">
                                                        <i class="fas fa-plus"></i> Recipient Name
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="insertVariable('{{sender_name}}')">
                                                        <i class="fas fa-plus"></i> Sender Name
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="insertVariable('{{current_date}}')">
                                                        <i class="fas fa-plus"></i> Current Date
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="insertHorizontalRule()">
                                                        <i class="fas fa-minus"></i> Horizontal Line
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Update Template
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="previewTemplate()">
                                        <i class="fas fa-eye"></i> Preview
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

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="previewModalBody">
                <!-- Preview content will be loaded here -->
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
<!-- Summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.js"></script>

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

function insertVariable(variable) {
    $('#content').summernote('insertText', variable);
}

function insertHorizontalRule() {
    $('#content').summernote('insertNode', document.createElement('hr'));
}

function previewTemplate() {
    const title = $('#title').val();
    const content = $('#content').summernote('code');
    
    if (!title || !content) {
        alert('Please enter both title and content before previewing.');
        return;
    }
    
    // Replace template variables with sample data for preview
    let previewContent = content
        .replace(/{{recipient_name}}/g, 'John Doe')
        .replace(/{{recipient_email}}/g, 'john.doe@example.com')
        .replace(/{{sender_name}}/g, '<?php echo htmlspecialchars($user['name']); ?>')
        .replace(/{{sender_designation}}/g, 'Sample Designation')
        .replace(/{{current_date}}/g, new Date().toLocaleDateString())
        .replace(/{{current_time}}/g, new Date().toLocaleTimeString());
    
    $('#previewModalBody').html(`
        <h4>${title}</h4>
        <hr>
        <div class="border p-3" style="background-color: #f8f9fa;">
            ${previewContent}
        </div>
    `);
    
    $('#previewModal').modal('show');
}

function duplicateTemplate(templateId) {
    if (confirm('Are you sure you want to duplicate this template?')) {
        $.ajax({
            url: 'duplicate_template.php',
            method: 'POST',
            data: { template_id: templateId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Template duplicated successfully');
                    window.location.href = 'index.php';
                } else {
                    alert('Error duplicating template: ' + response.message);
                }
            },
            error: function() {
                alert('Error duplicating template');
            }
        });
    }
}

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