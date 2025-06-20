<?php
require_once '../classes/Auth.php';
require_once '../classes/EmailTemplate.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$emailTemplate = new EmailTemplate();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search) {
    $templates = $emailTemplate->searchTemplates($search, $limit, $offset);
} else {
    $templates = $emailTemplate->getAllTemplates($limit, $offset);
}

$total_templates = $emailTemplate->getTotalTemplates();
$total_pages = ceil($total_templates / $limit);

$success_message = '';
$error_message = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $template_id = $_GET['id'];
    if ($emailTemplate->deleteTemplate($template_id)) {
        $success_message = 'Template deleted successfully';
    } else {
        $error_message = 'Error deleting template';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Email Templates</title>

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
        .template-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                        <h1 class="m-0">Email Templates</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Email Templates</li>
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

                <!-- Search and Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-search mr-1"></i>
                            Search & Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form method="get" action="" class="d-flex">
                                    <input type="text" class="form-control" name="search" placeholder="Search templates..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary ms-2">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <?php if ($search): ?>
                                        <a href="index.php" class="btn btn-secondary ms-2">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="create.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Create New Template
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Templates Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt mr-1"></i>
                            Email Templates (<?php echo $total_templates; ?> total)
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Content Preview</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($templates)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No templates found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($templates as $template): ?>
                                        <tr>
                                            <td><?php echo $template['id']; ?></td>
                                            <td><?php echo htmlspecialchars($template['title']); ?></td>
                                            <td class="template-preview"><?php echo htmlspecialchars(strip_tags($template['content'])); ?></td>
                                            <td>
                                                <?php if ($template['status'] == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($template['created_by_name'] ?? 'Unknown'); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($template['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#templateModal" onclick="viewTemplate(<?php echo $template['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="edit.php?id=<?php echo $template['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="duplicateTemplate(<?php echo $template['id']; ?>)">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteTemplate(<?php echo $template['id']; ?>, '<?php echo htmlspecialchars($template['title']); ?>')">
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
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>">
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

<!-- Template View Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalLabel">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="templateModalBody">
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
function viewTemplate(templateId) {
    $('#templateModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');
    
    $.ajax({
        url: 'view_template.php',
        method: 'POST',
        data: { template_id: templateId },
        success: function(response) {
            $('#templateModalBody').html(response);
        },
        error: function() {
            $('#templateModalBody').html('<div class="alert alert-danger">Error loading template</div>');
        }
    });
}

function deleteTemplate(templateId, templateTitle) {
    if (confirm('Are you sure you want to delete the template "' + templateTitle + '"?')) {
        window.location.href = 'index.php?action=delete&id=' + templateId;
    }
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
                    location.reload();
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

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>
</body>
</html>