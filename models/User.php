<?php
/**
 * User Model
 */

class User {
    private $conn;
    private $table = 'users';
    
    public $user_id;
    public $full_name;
    public $email;
    public $phone;
    public $password;
    public $role;
    public $status;
    public $email_verified;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new user
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 (full_name, email, phone, password, role, status) 
                 VALUES (:full_name, :email, :phone, :password, :role, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind parameters
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists() {
        $query = "SELECT user_id, full_name, email, phone, password, role, status 
                 FROM " . $this->table . " 
                 WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $this->user_id = $row['user_id'];
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }
    
    /**
     * Get all users
     */
    public function getAll($role = null) {
        if ($role) {
            $query = "SELECT * FROM " . $this->table . " WHERE role = :role ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':role', $role);
        } else {
            $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Update user
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                 SET full_name = :full_name, 
                     email = :email, 
                     phone = :phone 
                 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Update password
     */
    public function updatePassword($user_id, $new_password) {
        $query = "UPDATE " . $this->table . " SET password = :password WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Update user status
     */
    public function updateStatus($user_id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete user
     */
    public function delete($user_id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get user count
     */
    public function getCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE role = 'user'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
    
    /**
     * Get all customers (users with role 'user')
     */
    public function getAllCustomers() {
        $query = "SELECT user_id, full_name, email, phone, status 
                 FROM " . $this->table . " 
                 WHERE role = 'user' AND status = 'active' 
                 ORDER BY full_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
