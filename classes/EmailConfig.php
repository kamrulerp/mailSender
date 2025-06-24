<?php
require_once '../config/database.php';
require_once '../mail/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailConfig {
    private $conn;
    private $table_name = "email_configs";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllConfigs($limit = 50, $offset = 0) {
        $query = "SELECT ec.*, u.full_name as created_by_name FROM " . $this->table_name . " ec LEFT JOIN users u ON ec.created_by = u.id ORDER BY ec.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveConfigs() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY config_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConfigById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createConfig($config_name, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $encryption_type, $email_address, $cc_mail, $from_name, $country, $category, $status, $created_by) {


        $query = "INSERT INTO " . $this->table_name . " (config_name, email_address, smtp_host, smtp_port, smtp_username, smtp_password, encryption_type, cc_mail, from_name, country, category, status, created_by) VALUES (:config_name, :email_address, :smtp_host, :smtp_port, :smtp_username, :smtp_password, :encryption_type, :cc_mail, :from_name, :country, :category, :status, :created_by)";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':config_name', $config_name);
        $stmt->bindParam(':email_address', $email_address);
        $stmt->bindParam(':smtp_host', $smtp_host);
        $stmt->bindParam(':smtp_port', $smtp_port);
        $stmt->bindParam(':smtp_username', $smtp_username);
        $stmt->bindParam(':smtp_password', $smtp_password);
        $stmt->bindParam(':encryption_type', $encryption_type);
        $stmt->bindParam(':cc_mail', $cc_mail);
        $stmt->bindParam(':from_name', $from_name);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':created_by', $created_by);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateConfig($id, $config_name, $email_address, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $encryption_type, $cc_mail, $from_name, $country, $category, $status) {
        $query = "UPDATE " . $this->table_name . " SET config_name = :config_name, email_address = :email_address, smtp_host = :smtp_host, smtp_port = :smtp_port, smtp_username = :smtp_username, smtp_password = :smtp_password, encryption_type = :encryption_type, cc_mail = :cc_mail,from_name = :from_name, country = :country, category = :category, status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':config_name', $config_name);
        $stmt->bindParam(':email_address', $email_address);
        $stmt->bindParam(':smtp_host', $smtp_host);
        $stmt->bindParam(':smtp_port', $smtp_port);
        $stmt->bindParam(':smtp_username', $smtp_username);
        $stmt->bindParam(':smtp_password', $smtp_password);
        $stmt->bindParam(':encryption_type', $encryption_type);
        $stmt->bindParam(':cc_mail', $cc_mail);
        $stmt->bindParam(':from_name', $from_name);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    public function deleteConfig($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function testConnection($config_id) {
        $config = $this->getConfigById($config_id);
        if (!$config) {
            return ['success' => false, 'message' => 'Configuration not found'];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = $config['encryption_type'] === 'ssl' ? 'ssl' : 'tls';
            $mail->Port = $config['smtp_port'];
            
            $mail->SMTPDebug = 0;
            $mail->Timeout = 10;
            
            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                return ['success' => true, 'message' => 'Connection successful'];
            } else {
                return ['success' => false, 'message' => 'Failed to connect to SMTP server'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }
}
?>