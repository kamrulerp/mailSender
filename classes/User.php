<?php
require_once '../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllUsers($limit = 50, $offset = 0) {
        $query = "SELECT id, username, email, role, full_name, designation, status, country, categories, created_at FROM " . $this->table_name . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Decode JSON categories for each user
        foreach ($users as &$user) {
            $user['categories'] = json_decode($user['categories'], true) ?? [];
        }
        return $users;
    }

    public function getUserById($id) {
        $query = "SELECT id, username, email, role, full_name, designation, status, country, categories, created_at FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user['categories'] = json_decode($user['categories'], true) ?? [];
        }
        return $user;
    }

    public function createUser($full_name, $email, $password, $role, $status, $country = null, $categories = []) {
        // Check if email already exists
        $check_query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            return false; // User already exists
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $username = $email; // Use email as username
        $categories_json = json_encode($categories);
        
        $query = "INSERT INTO " . $this->table_name . " (username, email, password, role, full_name, status, country, categories) VALUES (:username, :email, :password, :role, :full_name, :status, :country, :categories)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':categories', $categories_json);

        return $stmt->execute();
    }

    public function updateUser($id, $username, $email, $role, $full_name, $designation, $status, $country = null, $categories = []) {
        $categories_json = json_encode($categories);
        
        $query = "UPDATE " . $this->table_name . " SET username = :username, email = :email, role = :role, full_name = :full_name, designation = :designation, status = :status, country = :country, categories = :categories WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':designation', $designation);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':categories', $categories_json);

        return $stmt->execute();
    }

    public function updateProfile($id, $full_name, $email, $password = null) {
        if ($password) {
            $query = "UPDATE " . $this->table_name . " SET full_name = :full_name, email = :email, password = :password WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $password);
        } else {
            $query = "UPDATE " . $this->table_name . " SET full_name = :full_name, email = :email WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }

    public function deleteUser($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function changePassword($id, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $hashed_password);
        return $stmt->execute();
    }

    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function searchUsers($search_term, $limit = 50, $offset = 0) {
        $search_term = "%" . $search_term . "%";
        $query = "SELECT id, username, email, role, full_name, designation, status, created_at FROM " . $this->table_name . " WHERE username LIKE :search OR email LIKE :search OR full_name LIKE :search ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':search', $search_term);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

    public function updateUserProfile($id, $full_name, $email, $password = null) {
        if ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE " . $this->table_name . " SET full_name = :full_name, email = :email, password = :password, username = :email WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
        } else {
            $query = "UPDATE " . $this->table_name . " SET full_name = :full_name, email = :email, username = :email WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }
}
?>