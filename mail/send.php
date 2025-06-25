<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailConfig.php';
require_once '../classes/EmailTemplate.php';
require_once '../classes/MailSender.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$emailConfig = new EmailConfig();
$emailTemplate = new EmailTemplate();
$mailSender = new MailSender();

$configs = $emailConfig->getActiveConfigs();
$templates = $emailTemplate->getActiveTemplates();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $config_id = $_POST['config_id'];
    $to_email = trim($_POST['to_email']);
    $subject = trim($_POST['subject']);
    $content = $_POST['content'];
    $footer = $_POST['footer'] ?? '';
    $sender_name = trim($_POST['sender_name']);
    $sender_designation = trim($_POST['sender_designation']);
    $template_id = !empty($_POST['template_id']) ? $_POST['template_id'] : null;
    
    if (empty($config_id) || empty($to_email) || empty($subject) || empty($content)) {
        $error_message = 'Please fill in all required fields';
    } elseif (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } else {
        $result = $mailSender->sendEmail($config_id, $to_email, $subject, $content, $footer, $sender_name, $sender_designation, $template_id, $user['id']);
        
        if ($result['success']) {
            $success_message = $result['message'];
            // Clear form data
            $_POST = [];
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Send Email</title>

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
                        <h1 class="m-0">Send Email</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Send Email</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-paper-plane mr-1"></i>
                                    Compose Email
                                </h3>
                            </div>
                            
                            <?php if ($success_message): ?>
                                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="config_id">From Email Configuration <span class="text-danger">*</span></label>
                                                <select class="form-control" id="config_id" name="config_id" required>
                                                    <option value="">Select Email Configuration</option>
                                                    <?php foreach ($configs as $config): ?>
                                                        <option value="<?php echo $config['id']; ?>" <?php echo (isset($_POST['config_id']) && $_POST['config_id'] == $config['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($config['config_name'] . ' (' . $config['email_address'] . ')'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="to_email">To Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="to_email" name="to_email" placeholder="recipient@example.com" value="<?php echo htmlspecialchars($_POST['to_email'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="subject">Subject <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Email subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="template_id">Email Template (Optional)</label>
                                                <select class="form-control" id="template_id" name="template_id">
                                                    <option value="">Select Template (Optional)</option>
                                                    <?php foreach ($templates as $template): ?>
                                                        <option value="<?php echo $template['id']; ?>" <?php echo (isset($_POST['template_id']) && $_POST['template_id'] == $template['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($template['title']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-info btn-block" id="loadTemplate">Load Selected Template</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="content">Email Content <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="footer">Email Footer</label>
                                        <textarea class="form-control" id="footer" name="footer" rows="3" placeholder="Additional footer content"><?php echo htmlspecialchars($_POST['footer'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="sender_name">Sender Name</label>
                                                <input type="text" class="form-control" id="sender_name" name="sender_name" placeholder="Your name" value="<?php echo htmlspecialchars($_POST['sender_name'] ?? $user['full_name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="sender_designation">Sender Designation</label>
                                                <input type="text" class="form-control" id="sender_designation" name="sender_designation" placeholder="Your designation" value="<?php echo htmlspecialchars($_POST['sender_designation'] ?? $user['designation']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Send Email
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <a href="../dashboard/index.php" class="btn btn-default">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
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
<!-- Summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Summernote
    $('#content').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
    
    $('#footer').summernote({
        height: 150,
        toolbar: [
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']]
        ]
    });
    
    // Load template content
    $('#loadTemplate').click(function() {
        var templateId = $('#template_id').val();
        if (templateId) {
            $.ajax({
                url: 'get_template.php',
                method: 'POST',
                data: { template_id: templateId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#content').summernote('code', response.content);
                    } else {
                        alert('Error loading template: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error loading template');
                }
            });
        } else {
            alert('Please select a template first');
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
</body>
</html>