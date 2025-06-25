<?php
require_once '../config/database.php';

class Auth {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password) {
        $query = "SELECT id, username, email, password, role, full_name, designation, status FROM " . $this->table_name . " WHERE (username = :username OR email = :username) AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['designation'] = $row['designation'];
                $_SESSION['logged_in'] = true;
                return true;
            }
        }
        return false;
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function hasRole($roles) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        if (is_string($roles)) {
            $roles = [$roles];
        }
        return in_array($_SESSION['role'], $roles);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ../auth/login.php');
            exit();
        }
    }

    public function requireRole($roles) {
        $this->requireLogin();
        if (!$this->hasRole($roles)) {
            header('Location: ../dashboard/index.php?error=access_denied');
            exit();
        }
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $query = "SELECT id, username, email, role, full_name, designation, status, country, categories FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user['categories'] = json_decode($user['categories'], true) ?? [];
        }
        return $user;
    }

    public function canAccessCountryMenu($country) {
        $user = $this->getCurrentUser();
        if (!$user || $user['role'] !== 'user') {
            return true; // Admin and super_admin can access all
        }
        
        return $user['country'] === $country;
    }

    public function canAccessCategoryMenu($category) {
        $user = $this->getCurrentUser();
        if (!$user || $user['role'] !== 'user') {
            return true; // Admin and super_admin can access all
        }
        
        return in_array($category, $user['categories'] ?? []);
    }

    public function createUser($username, $email, $password, $role, $full_name, $designation = '') {
        // Check if username or email already exists
        $check_query = "SELECT id FROM " . $this->table_name . " WHERE username = :username OR email = :email";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            return false; // User already exists
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO " . $this->table_name . " (username, email, password, role, full_name, designation) VALUES (:username, :email, :password, :role, :full_name, :designation)";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':designation', $designation);

        return $stmt->execute();
    }

    public function getCountryList() {
        return [
            'Australia',
            'Newzealand',
            'Denmark',
            'Canada',
            'Ireland'
        ];
    }

    public function getCategoryList() {
        return [
            'File Submission',
            'Payment Received',
            'Job Offer letter',
            'Required Documents',
            'Normal Rejection',
            'Notary Error Rejection',
            'Company Rejection',
        ];
    }
}
?>