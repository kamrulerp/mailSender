<?php
require_once '../config/database.php';

class EmailTemplate {
    private $conn;
    private $table_name = "email_templates";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllTemplates($limit = 50, $offset = 0) {
        $query = "SELECT et.*, u.full_name as created_by_name FROM " . $this->table_name . " et LEFT JOIN users u ON et.created_by = u.id ORDER BY et.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveTemplates() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY title";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTemplateById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createTemplate($title, $content, $status, $created_by) {
        $query = "INSERT INTO " . $this->table_name . " (title, content, status, created_by) VALUES (:title, :content, :status, :created_by)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':created_by', $created_by);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateTemplate($id, $title, $content, $status) {
        $query = "UPDATE " . $this->table_name . " SET title = :title, content = :content, status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    public function deleteTemplate($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function searchTemplates($search_term, $limit = 50, $offset = 0) {
        $search_term = "%" . $search_term . "%";
        $query = "SELECT et.*, u.full_name as created_by_name FROM " . $this->table_name . " et LEFT JOIN users u ON et.created_by = u.id WHERE et.title LIKE :search OR et.content LIKE :search ORDER BY et.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':search', $search_term);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalTemplates() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function duplicateTemplate($id, $created_by) {
        $template = $this->getTemplateById($id);
        if (!$template) {
            return false;
        }

        $new_title = $template['title'] . ' (Copy)';
        return $this->createTemplate($new_title, $template['content'], $template['status'], $created_by);
    }
}
?>