<?php
/**
 * Service Model
 */

class Service {
    private $conn;
    private $table = 'services';
    
    public $service_id;
    public $service_name;
    public $description;
    public $duration;
    public $price;
    public $image_url;
    public $status;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new service
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 (service_name, description, duration, price, image_url, status) 
                 VALUES (:service_name, :description, :duration, :price, :image_url, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':service_name', $this->service_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':duration', $this->duration);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':status', $this->status);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    /**
     * Get all services
     */
    public function getAll($status = null) {
        if ($status) {
            $query = "SELECT * FROM " . $this->table . " WHERE status = :status ORDER BY service_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
        } else {
            $query = "SELECT * FROM " . $this->table . " ORDER BY service_name";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get service by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE service_id = :service_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service_id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }
    
    /**
     * Update service
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                 SET service_name = :service_name, 
                     description = :description, 
                     duration = :duration, 
                     price = :price, 
                     image_url = :image_url, 
                     status = :status 
                 WHERE service_id = :service_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':service_name', $this->service_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':duration', $this->duration);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':service_id', $this->service_id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete service
     */
    public function delete($service_id) {
        $query = "DELETE FROM " . $this->table . " WHERE service_id = :service_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service_id', $service_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get service count
     */
    public function getCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
}
?>
