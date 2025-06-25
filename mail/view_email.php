<?php
require_once '../classes/Auth.php';
require_once '../classes/MailSender.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo '<div class="alert alert-danger">Unauthorized</div>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['email_id'])) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit();
}

$email_id = $_POST['email_id'];
$mailSender = new MailSender();

$email = $mailSender->getMailById($email_id);

if (!$email) {
    echo '<div class="alert alert-danger">Email not found</div>';
    exit();
}
?>

<div class="row">
    <div class="col-md-6">
        <strong>From:</strong> <?php echo htmlspecialchars($email['from_name'] . ' <' . $email['from_email'] . '>'); ?>
    </div>
    <div class="col-md-6">
        <strong>To:</strong> <?php echo htmlspecialchars($email['to_email']); ?>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-6">
        <strong>Subject:</strong> <?php echo htmlspecialchars($email['subject']); ?>
    </div>
    <div class="col-md-6">
        <strong>Status:</strong> 
        <?php if ($email['status'] == 'sent'): ?>
            <span class="badge bg-success">Sent</span>
        <?php elseif ($email['status'] == 'failed'): ?>
            <span class="badge bg-danger">Failed</span>
        <?php else: ?>
            <span class="badge bg-warning">Pending</span>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-6">
        <strong>Sent At:</strong> <?php echo date('M d, Y H:i:s', strtotime($email['sent_at'])); ?>
    </div>
    <div class="col-md-6">
        <strong>Sent By:</strong> <?php echo htmlspecialchars($email['sent_by_name'] ?? 'Unknown'); ?>
    </div>
</div>

<?php if ($email['template_title']): ?>
<div class="row mt-2">
    <div class="col-md-6">
        <strong>Template Used:</strong> <?php echo htmlspecialchars($email['template_title']); ?>
    </div>
    <div class="col-md-6">
        <strong>Config Used:</strong> <?php echo htmlspecialchars($email['config_name'] ?? 'Unknown'); ?>
    </div>
</div>
<?php endif; ?>

<?php if ($email['sender_name'] || $email['sender_designation']): ?>
<div class="row mt-2">
    <div class="col-md-6">
        <strong>Sender Name:</strong> <?php echo htmlspecialchars($email['sender_name'] ?? 'N/A'); ?>
    </div>
    <div class="col-md-6">
        <strong>Sender Designation:</strong> <?php echo htmlspecialchars($email['sender_designation'] ?? 'N/A'); ?>
    </div>
</div>
<?php endif; ?>

<?php if ($email['status'] == 'failed' && $email['error_message']): ?>
<div class="row mt-2">
    <div class="col-md-12">
        <strong>Error Message:</strong>
        <div class="alert alert-danger mt-1">
            <?php echo htmlspecialchars($email['error_message']); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<hr>

<div class="row">
    <div class="col-md-12">
        <strong>Email Content:</strong>
        <div class="border p-3 mt-2" style="background-color: #f8f9fa; max-height: 400px; overflow-y: auto;">
            <?php echo $email['content']; ?>
            
            <?php if ($email['footer']): ?>
                <hr>
                <?php echo $email['footer']; ?>
            <?php endif; ?>
            
            <?php if ($email['sender_name'] || $email['sender_designation']): ?>
                <br><br>
                <strong>Best regards,</strong><br>
                <?php if ($email['sender_name']): ?>
                    <?php echo htmlspecialchars($email['sender_name']); ?><br>
                <?php endif; ?>
                <?php if ($email['sender_designation']): ?>
                    <?php echo htmlspecialchars($email['sender_designation']); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>