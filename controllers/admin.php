<?php
class AdminController{
    public function set_availability(){
        global $conn;
        date_default_timezone_set('Asia/Manila');
        $response = array();

        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = htmlspecialchars(isset($data['user_id']) ? $data['user_id'] : '');
        $availability_id = bin2hex(random_bytes(16));
        $start_time = htmlspecialchars(isset($data['start_time']) ? $data['start_time'] : '');
        $end_time = htmlspecialchars(isset($data['end_time']) ? $data['end_time'] : '');
        $status = htmlspecialchars(isset($data['status']) ? $data['status'] : '');
        $created_at = date('Y-m-d H:i:s');

        if(empty($user_id)){
            $response['status'] = 'error';
            $response['message'] = 'User ID cannot be empty';
            echo json_encode($response);
            return;
        }

        // Check if user exists and has the role 'doctor'
        $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt->close();
            $response['status'] = 'error';
            $response['message'] = 'User ID does not exist';
            echo json_encode($response);
            return;
        }

        $user = $result->fetch_assoc();
        if ($user['role'] !== 'doctor') {
            $stmt->close();
            $response['status'] = 'error';
            $response['message'] = 'You are not a doctor';
            echo json_encode($response);
            return;
        }

        $stmt->close();

        if(empty($start_time)){
            $response['status'] = 'error';
            $response['message'] = 'Start time cannot be empty';
            echo json_encode($response);
            return;
        } else if (!$this->isValidDateTime($start_time)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid format for start time';
            echo json_encode($response);
            return;
        }

        if(empty($end_time)){
            $response['status'] = 'error';
            $response['message'] = 'End time cannot be empty';
            echo json_encode($response);
            return;
        } else if (!$this->isValidDateTime($end_time)) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid format for end time';
            echo json_encode($response);
            return;
        }

        if(empty($status)){
            $response['status'] = 'error';
            $response['message'] = 'Status cannot be empty';
            echo json_encode($response);
            return;
        }

        // Insert availability into the database
        $stmt = $conn->prepare("INSERT INTO availability (availability_id, user_id, start_time, end_time, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $availability_id, $user_id, $start_time, $end_time, $status, $created_at);
        
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Availability set successfully';
            echo json_encode($response);
            return;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error setting availability: ' . $conn->error;
            echo json_encode($response);
            return;
        }

        $stmt->close();
    }

    public function availability() {
        global $conn;
        date_default_timezone_set('Asia/Manila');
        $response = array();
    
        // Get user_id from the request
        $user_id = htmlspecialchars(isset($_GET['user_id']) ? $_GET['user_id'] : '');
    
        if (empty($user_id)) {
            $response['status'] = 'error';
            $response['message'] = 'User ID cannot be empty';
            echo json_encode($response);
            return;
        }
    
        // Check if the user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 0) {
            $stmt->close();
            $response['status'] = 'error';
            $response['message'] = 'User ID does not exist';
            echo json_encode($response);
            return;
        }
        $stmt->close();
    
        // Fetch availability records for the user
        $stmt = $conn->prepare("SELECT * FROM availability WHERE user_id = ? ORDER BY start_time ASC");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $availability = array();
            while ($row = $result->fetch_assoc()) {
                $availability[] = array(
                    'availability_id' => $row['availability_id'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'status' => $row['status']
                );
            }
            $response['status'] = 'success';
            $response['data'] = $availability;
            echo json_encode($response);
            return;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'No availability records found for this user';
            echo json_encode($response);
            return;
        }
    
        $stmt->close();
    }    

    public function appointment() {
        global $conn;
        date_default_timezone_set('Asia/Manila');
        $response = array();
    
        // Get user_id from request
        $user_id = htmlspecialchars(isset($_GET['user_id']) ? $_GET['user_id'] : '');
    
        if (empty($user_id)) {
            $response['status'] = 'error';
            $response['message'] = 'User ID cannot be empty';
            echo json_encode($response);
            return;
        }
    
        // Fetch appointments based on user_id
        $stmt = $conn->prepare("SELECT a.appointment_id, a.user_id, a.doctor_id, a.appointment_time, a.created_at, d.contact_number, d.name, d.email 
                                FROM appointments a 
                                JOIN users d ON a.user_id = d.user_id
                                WHERE a.doctor_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $appointments = array();
    
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
    
            $response['status'] = 'success';
            $response['appointments'] = $appointments;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'No appointments found for this user';
        }
    
        echo json_encode($response);
        $stmt->close();
    }
    
    private function isValidDateTime($datetime, $format = 'Y-m-d H:i:s') {
        $dt = DateTime::createFromFormat($format, $datetime);
        return $dt && $dt->format($format) === $datetime;
    }
}
?>