<?php
require_once '../classes/Auth.php';
require_once '../classes/MailSender.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$mailSender = new MailSender();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filters
$filters = [];
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['from_date'])) {
    $filters['from_date'] = $_GET['from_date'];
}
if (!empty($_GET['to_date'])) {
    $filters['to_date'] = $_GET['to_date'];
}
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

$emails = $mailSender->getMailHistory($limit, $offset, $filters);
$total_emails = $mailSender->getTotalMailHistory($filters);
$total_pages = ceil($total_emails / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mail Sender | Email History</title>

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
        .table th {
            border-top: none;
        }
        .email-preview {
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
                        <h1 class="m-0">Email History</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Email History</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter mr-1"></i>
                            Filters
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="get" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">All Status</option>
                                            <option value="sent" <?php echo (isset($_GET['status']) && $_GET['status'] == 'sent') ? 'selected' : ''; ?>>Sent</option>
                                            <option value="failed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="from_date">From Date</label>
                                        <input type="date" class="form-control" id="from_date" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="to_date">To Date</label>
                                        <input type="date" class="form-control" id="to_date" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search">Search</label>
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Email, subject..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="history.php" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Clear
                                    </a>
                                    <a href="send.php" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Send New Email
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Email History Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-1"></i>
                            Email History (<?php echo $total_emails; ?> total)
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>To Email</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                    <th>Sent By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($emails)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No emails found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($emails as $email): ?>
                                        <tr>
                                            <td><?php echo $email['id']; ?></td>
                                            <td><?php echo htmlspecialchars($email['to_email']); ?></td>
                                            <td class="email-preview"><?php echo htmlspecialchars($email['subject']); ?></td>
                                            <td>
                                                <?php if ($email['status'] == 'sent'): ?>
                                                    <span class="badge bg-success">Sent</span>
                                                <?php elseif ($email['status'] == 'failed'): ?>
                                                    <span class="badge bg-danger">Failed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($email['sent_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($email['sent_by_name'] ?? 'Unknown'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#emailModal" onclick="viewEmail(<?php echo $email['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
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
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>&<?php echo http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)); ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>&<?php echo http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)); ?>">
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

<!-- Email View Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">Email Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="emailModalBody">
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
function viewEmail(emailId) {
    $('#emailModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');
    
    $.ajax({
        url: 'view_email.php',
        method: 'POST',
        data: { email_id: emailId },
        success: function(response) {
            $('#emailModalBody').html(response);
        },
        error: function() {
            $('#emailModalBody').html('<div class="alert alert-danger">Error loading email details</div>');
        }
    });
}
</script>
</body>
</html>