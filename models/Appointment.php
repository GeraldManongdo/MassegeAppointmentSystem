<?php
/**
 * Appointment Model
 */

class Appointment {
    private $conn;
    private $table = 'appointments';
    
    public $appointment_id;
    public $user_id;
    public $service_id;
    public $appointment_date;
    public $start_time;
    public $end_time;
    public $status;
    public $cancellation_reason;
    public $notes;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new appointment
     */
    public function create() {
        // Check for conflicts before creating
        if ($this->hasConflict()) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table . " 
                 (user_id, service_id, appointment_date, start_time, end_time, status, notes) 
                 VALUES (:user_id, :service_id, :appointment_date, :start_time, :end_time, :status, :notes)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':service_id', $this->service_id);
        $stmt->bindParam(':appointment_date', $this->appointment_date);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':notes', $this->notes);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    /**
     * Check if there's a time slot conflict
     */
    public function hasConflict() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                 WHERE appointment_date = :appointment_date 
                 AND status IN ('confirmed', 'pending')
                 AND (
                     (start_time <= :start_time AND end_time > :start_time)
                     OR (start_time < :end_time AND end_time >= :end_time)
                     OR (start_time >= :start_time AND end_time <= :end_time)
                 )";
        
        if (isset($this->appointment_id)) {
            $query .= " AND appointment_id != :appointment_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':appointment_date', $this->appointment_date);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        
        if (isset($this->appointment_id)) {
            $stmt->bindParam(':appointment_id', $this->appointment_id);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        
        return $row['count'] > 0;
    }
    
    /**
     * Get all appointments
     */
    public function getAll($filters = []) {
        $query = "SELECT a.*, u.full_name, u.email, u.phone, s.service_name, s.price 
                 FROM " . $this->table . " a 
                 INNER JOIN users u ON a.user_id = u.user_id 
                 INNER JOIN services s ON a.service_id = s.service_id 
                 WHERE 1=1";
        
        if (!empty($filters['date'])) {
            $query .= " AND a.appointment_date = :date";
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND a.status = :status";
        }
        
        if (!empty($filters['service_id'])) {
            $query .= " AND a.service_id = :service_id";
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND a.user_id = :user_id";
        }
        
        $query .= " ORDER BY a.appointment_date DESC, a.start_time DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filters['date'])) {
            $stmt->bindParam(':date', $filters['date']);
        }
        
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        
        if (!empty($filters['service_id'])) {
            $stmt->bindParam(':service_id', $filters['service_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $stmt->bindParam(':user_id', $filters['user_id']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get appointment by ID
     */
    public function getById($id) {
        $query = "SELECT a.*, u.full_name, u.email, u.phone, s.service_name, s.duration, s.price 
                 FROM " . $this->table . " a 
                 INNER JOIN users u ON a.user_id = u.user_id 
                 INNER JOIN services s ON a.service_id = s.service_id 
                 WHERE a.appointment_id = :appointment_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':appointment_id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }
    
    /**
     * Get user appointments
     */
    public function getUserAppointments($user_id, $status = null) {
        $query = "SELECT a.*, s.service_name, s.duration, s.price 
                 FROM " . $this->table . " a 
                 INNER JOIN services s ON a.service_id = s.service_id 
                 WHERE a.user_id = :user_id";
        
        if ($status) {
            $query .= " AND a.status = :status";
        }
        
        $query .= " ORDER BY a.appointment_date DESC, a.start_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get upcoming appointment for user
     */
    public function getUpcomingAppointment($user_id) {
        $query = "SELECT a.*, s.service_name, s.duration, s.price 
                 FROM " . $this->table . " a 
                 INNER JOIN services s ON a.service_id = s.service_id 
                 WHERE a.user_id = :user_id 
                 AND a.status = 'confirmed' 
                 AND a.appointment_date >= CURDATE() 
                 ORDER BY a.appointment_date ASC, a.start_time ASC 
                 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }
    
    /**
     * Update appointment
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                 SET appointment_date = :appointment_date, 
                     start_time = :start_time, 
                     end_time = :end_time, 
                     status = :status, 
                     notes = :notes 
                 WHERE appointment_id = :appointment_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':appointment_date', $this->appointment_date);
        $stmt->bindParam(':start_time', $this->start_time);
        $stmt->bindParam(':end_time', $this->end_time);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':appointment_id', $this->appointment_id);
        
        return $stmt->execute();
    }
    
    /**
     * Cancel appointment
     */
    public function cancel($appointment_id, $reason = null) {
        $query = "UPDATE " . $this->table . " 
                 SET status = 'cancelled', 
                     cancellation_reason = :reason, 
                     cancelled_at = NOW() 
                 WHERE appointment_id = :appointment_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':appointment_id', $appointment_id);
        
        return $stmt->execute();
    }
    
    /**
     * Update status
     */
    public function updateStatus($appointment_id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE appointment_id = :appointment_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':appointment_id', $appointment_id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete appointment
     */
    public function delete($appointment_id) {
        $query = "DELETE FROM " . $this->table . " WHERE appointment_id = :appointment_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':appointment_id', $appointment_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get appointment statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total appointments
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        $stats['total'] = $row['total'];
        
        // Today's appointments
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                 WHERE appointment_date = CURDATE() AND status IN ('confirmed', 'pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        $stats['today'] = $row['total'];
        
        // Pending confirmations
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        $stats['pending'] = $row['total'];
        
        // Completed this month
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                 WHERE status = 'completed' AND MONTH(appointment_date) = MONTH(CURDATE()) 
                 AND YEAR(appointment_date) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        $stats['completed_month'] = $row['total'];
        
        return $stats;
    }
    
    /**
     * Get available time slots for a date and service
     */
    public function getAvailableTimeSlots($date, $service_id) {
        // Get service details
        $service_query = "SELECT duration FROM services WHERE service_id = :service_id";
        $service_stmt = $this->conn->prepare($service_query);
        $service_stmt->bindParam(':service_id', $service_id);
        $service_stmt->execute();
        $service = $service_stmt->fetch();
        
        if (!$service) {
            return [];
        }
        
        $duration = $service['duration'];
        
        // Get business hours for the day
        $day_of_week = date('w', strtotime($date));
        $hours_query = "SELECT * FROM business_hours WHERE day_of_week = :day_of_week";
        $hours_stmt = $this->conn->prepare($hours_query);
        $hours_stmt->bindParam(':day_of_week', $day_of_week);
        $hours_stmt->execute();
        $business_hours = $hours_stmt->fetch();
        
        if (!$business_hours || $business_hours['is_closed']) {
            return [];
        }
        
        // Get booked appointments for this date
        $booked_query = "SELECT start_time, end_time FROM " . $this->table . " 
                        WHERE appointment_date = :date 
                        AND status IN ('confirmed', 'pending')";
        $booked_stmt = $this->conn->prepare($booked_query);
        $booked_stmt->bindParam(':date', $date);
        $booked_stmt->execute();
        $booked_slots = $booked_stmt->fetchAll();
        
        // Generate time slots
        $slots = [];
        $current_time = strtotime($business_hours['opening_time']);
        $closing_time = strtotime($business_hours['closing_time']);
        
        while ($current_time < $closing_time) {
            $slot_start = date('H:i:s', $current_time);
            $slot_end = date('H:i:s', $current_time + ($duration * 60));
            
            // Check if slot extends beyond closing time
            if (strtotime($slot_end) > $closing_time) {
                break;
            }
            
            // Check if slot is available
            $is_available = true;
            foreach ($booked_slots as $booked) {
                $booked_start = strtotime($booked['start_time']);
                $booked_end = strtotime($booked['end_time']);
                $check_start = $current_time;
                $check_end = strtotime($slot_end);
                
                // Check for overlap
                if (($check_start >= $booked_start && $check_start < $booked_end) ||
                    ($check_end > $booked_start && $check_end <= $booked_end) ||
                    ($check_start <= $booked_start && $check_end >= $booked_end)) {
                    $is_available = false;
                    break;
                }
            }
            
            $slots[] = [
                'start_time' => $slot_start,
                'end_time' => $slot_end,
                'available' => $is_available,
                'display_time' => date('g:i A', $current_time)
            ];
            
            // Move to next slot (30 minutes interval)
            $current_time += 1800; // 30 minutes
        }
        
        return $slots;
    }
}
?>
