<?php
require_once '../config/database.php';
require_once '../mail/vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailSender {
    private $conn;
    private $history_table = "mail_history";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function sendEmail($config_id, $to_email, $subject, $content, $footer, $sender_name, $sender_designation, $template_id, $sent_by) {
        try {
            // Get email configuration
            $config_query = "SELECT * FROM email_configs WHERE id = :config_id AND status = 'active'";
            $config_stmt = $this->conn->prepare($config_query);
            $config_stmt->bindParam(':config_id', $config_id);
            $config_stmt->execute();
            $config = $config_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$config) {
                throw new \Exception('Email configuration not found or inactive');
            }

            // Prepare email content
            $email_body = $content;
            if (!empty($footer)) {
                $email_body .= '<br><br>' . $footer;
            }
            if (!empty($sender_name) || !empty($sender_designation)) {
                $email_body .= '<br><br>Best regards,<br>';
                if (!empty($sender_name)) {
                    $email_body .= $sender_name;
                }
                if (!empty($sender_designation)) {
                    $email_body .= '<br>' . $sender_designation;
                }
            }

            // Create PHPMailer instance
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            if ($config['encryption_type'] === 'ssl') {
                $mail->SMTPSecure = 'ssl';
            } elseif ($config['encryption_type'] === 'tls') {
                $mail->SMTPSecure = 'tls';
            }
            $mail->Port = $config['smtp_port'];
            
            // Set timeout settings to prevent hanging
            $mail->Timeout = 30;
            $mail->SMTPKeepAlive = false;
            
            // Disable SSL certificate verification to prevent connection issues
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Disable debug output for production
            $mail->SMTPDebug = 0;

            // Recipients
            $mail->setFrom($config['email_address'], $config['from_name']);
            $mail->addAddress($to_email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $email_body;

            // Send email
            $mail->send();

            // Save to history with success status
            $this->saveToHistory($config['email_address'], $config['from_name'], $to_email, $subject, $content, $footer, $sender_name, $sender_designation, $template_id, $config_id, $sent_by, 'sent', null);

            return ['success' => true, 'message' => 'Email sent successfully'];

        } catch (Exception $e) {
            // Save to history with failed status
            $this->saveToHistory($config['email_address'] ?? '', $config['from_name'] ?? '', $to_email, $subject, $content, $footer, $sender_name, $sender_designation, $template_id, $config_id, $sent_by, 'failed', $e->getMessage());

            return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
        }
    }

    private function saveToHistory($from_email, $from_name, $to_email, $subject, $content, $footer, $sender_name, $sender_designation, $template_id, $config_id, $sent_by, $status, $error_message) {
        $query = "INSERT INTO " . $this->history_table . " (from_email, from_name, to_email, subject, content, footer, sender_name, sender_designation, template_id, config_id, sent_by, status, error_message) VALUES (:from_email, :from_name, :to_email, :subject, :content, :footer, :sender_name, :sender_designation, :template_id, :config_id, :sent_by, :status, :error_message)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':from_email', $from_email);
        $stmt->bindParam(':from_name', $from_name);
        $stmt->bindParam(':to_email', $to_email);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':footer', $footer);
        $stmt->bindParam(':sender_name', $sender_name);
        $stmt->bindParam(':sender_designation', $sender_designation);
        $stmt->bindParam(':template_id', $template_id);
        $stmt->bindParam(':config_id', $config_id);
        $stmt->bindParam(':sent_by', $sent_by);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':error_message', $error_message);

        return $stmt->execute();
    }

    public function getMailHistory($limit = 50, $offset = 0, $filters = []) {
        $where_conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where_conditions[] = "mh.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from_date'])) {
            $where_conditions[] = "DATE(mh.sent_at) >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where_conditions[] = "DATE(mh.sent_at) <= :to_date";
            $params[':to_date'] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where_conditions[] = "(mh.to_email LIKE :search OR mh.subject LIKE :search OR mh.from_email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $query = "SELECT mh.*, u.full_name as sent_by_name, et.title as template_title, ec.config_name 
                  FROM " . $this->history_table . " mh 
                  LEFT JOIN users u ON mh.sent_by = u.id 
                  LEFT JOIN email_templates et ON mh.template_id = et.id 
                  LEFT JOIN email_configs ec ON mh.config_id = ec.id 
                  " . $where_clause . " 
                  ORDER BY mh.sent_at DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalMailHistory($filters = []) {
        $where_conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where_conditions[] = "status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['from_date'])) {
            $where_conditions[] = "DATE(sent_at) >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where_conditions[] = "DATE(sent_at) <= :to_date";
            $params[':to_date'] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where_conditions[] = "(to_email LIKE :search OR subject LIKE :search OR from_email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $query = "SELECT COUNT(*) as total FROM " . $this->history_table . " " . $where_clause;
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getMailById($id) {
        $query = "SELECT mh.*, u.full_name as sent_by_name, et.title as template_title, ec.config_name 
                  FROM " . $this->history_table . " mh 
                  LEFT JOIN users u ON mh.sent_by = u.id 
                  LEFT JOIN email_templates et ON mh.template_id = et.id 
                  LEFT JOIN email_configs ec ON mh.config_id = ec.id 
                  WHERE mh.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalEmailsByUser($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->history_table . " WHERE sent_by = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getSentEmailsByUser($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->history_table . " WHERE sent_by = :user_id AND status = 'sent'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getFailedEmailsByUser($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->history_table . " WHERE sent_by = :user_id AND status = 'failed'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getEmailsByUser($user_id, $limit = 50, $offset = 0) {
        $query = "SELECT mh.*, u.full_name as sent_by_name, et.title as template_title, ec.config_name 
                  FROM " . $this->history_table . " mh 
                  LEFT JOIN users u ON mh.sent_by = u.id 
                  LEFT JOIN email_templates et ON mh.template_id = et.id 
                  LEFT JOIN email_configs ec ON mh.config_id = ec.id 
                  WHERE mh.sent_by = :user_id 
                  ORDER BY mh.sent_at DESC 
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>