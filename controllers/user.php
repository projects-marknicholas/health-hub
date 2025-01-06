<?php 
class UserController {
    public function set_appointment() {
        global $conn;
        date_default_timezone_set('Asia/Manila');
        $response = array();
    
        // Get input data
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = htmlspecialchars($data['user_id'] ?? '');
        $doctor_id = htmlspecialchars($data['doctor_id'] ?? '');
        $appointment_date = htmlspecialchars($data['appointment_date'] ?? '');
        $appointment_time = htmlspecialchars($data['appointment_time'] ?? '');
        $appointment_id = bin2hex(random_bytes(16));
        $created_at = date('Y-m-d H:i:s');
    
        // Validation for empty inputs
        if (empty($user_id)) {
            $response['status'] = 'error';
            $response['message'] = 'User ID cannot be empty';
            echo json_encode($response);
            return;
        }
    
        if (empty($doctor_id)) {
            $response['status'] = 'error';
            $response['message'] = 'Doctor ID cannot be empty';
            echo json_encode($response);
            return;
        }
    
        if (empty($appointment_date)) {
            $response['status'] = 'error';
            $response['message'] = 'Appointment date cannot be empty';
            echo json_encode($response);
            return;
        }
    
        if (empty($appointment_time)) {
            $response['status'] = 'error';
            $response['message'] = 'Appointment time cannot be empty';
            echo json_encode($response);
            return;
        }
    
        // Validate appointment time format (HH:mm)
        if (!$this->isValidDateTime($appointment_time, 'H:i')) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid format for appointment time';
            echo json_encode($response);
            return;
        }
    
        // Combine appointment date and time
        $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    
        // Check if the doctor is available at the requested time
        $stmt = $conn->prepare(
            "SELECT * FROM availability 
            WHERE user_id = ? AND ? BETWEEN start_time AND end_time AND status = 'available'"
        );
        
        if ($stmt === false) {
            $response['status'] = 'error';
            $response['message'] = 'Failed to prepare statement for doctor availability check: ' . $conn->error;
            echo json_encode($response);
            return;
        }
    
        $stmt->bind_param("ss", $doctor_id, $appointment_datetime);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            $stmt->close();
            $response['status'] = 'error';
            $response['message'] = 'Doctor is not available at the requested time' . $appointment_datetime;
            echo json_encode($response);
            return;
        }
        $stmt->close();
    
        // Calculate the end time of the appointment (1-hour duration)
        $end_time = date('Y-m-d H:i:s', strtotime($appointment_datetime) + 3600);
    
        // Check if the requested time conflicts with existing appointments
        $stmt = $conn->prepare(
            "SELECT * FROM appointments 
            WHERE doctor_id = ? AND (
                (appointment_time < ? AND DATE_ADD(appointment_time, INTERVAL 1 HOUR) > ?) OR
                (appointment_time >= ? AND appointment_time < ?)
            )"
        );
        
        if ($stmt === false) {
            $response['status'] = 'error';
            $response['message'] = 'Failed to prepare statement for appointment conflict check: ' . $conn->error;
            echo json_encode($response);
            return;
        }
    
        $stmt->bind_param("sssss", $doctor_id, $end_time, $appointment_datetime, $appointment_datetime, $end_time);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $stmt->close();
            $response['status'] = 'error';
            $response['message'] = 'The requested appointment time conflicts with an existing appointment';
            echo json_encode($response);
            return;
        }
        $stmt->close();
    
        // Insert the appointment into the appointments table
        $stmt = $conn->prepare(
            "INSERT INTO appointments (appointment_id, user_id, doctor_id, appointment_time, created_at) 
            VALUES (?, ?, ?, ?, ?)"
        );
    
        if ($stmt === false) {
            $response['status'] = 'error';
            $response['message'] = 'Failed to prepare statement for appointment insertion: ' . $conn->error;
            echo json_encode($response);
            return;
        }
    
        $stmt->bind_param("sssss", $appointment_id, $user_id, $doctor_id, $appointment_datetime, $created_at);
    
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Appointment set successfully';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error setting appointment: ' . $stmt->error;
        }
    
        echo json_encode($response);
        $stmt->close();
    }                 
    
    public function delete_appointment() {
        global $conn;
        $response = array();
    
        // Get appointment_id and user_id from request
        $appointment_id = htmlspecialchars(isset($_GET['appointment_id']) ? $_GET['appointment_id'] : '');
        $user_id = htmlspecialchars(isset($_GET['user_id']) ? $_GET['user_id'] : '');
    
        // Check if appointment_id and user_id are provided
        if (empty($appointment_id) || empty($user_id)) {
            $response['status'] = 'error';
            $response['message'] = 'Appointment ID and User ID are required';
            echo json_encode($response);
            return;
        }
    
        // Check if the appointment exists and belongs to the user
        $stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND user_id = ?");
        $stmt->bind_param("ss", $appointment_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // If no matching appointment is found
        if ($result->num_rows == 0) {
            $stmt->close();
            $response['status'] = 'error';
            $response['message'] = 'Appointment not found or you do not have permission to delete this appointment';
            echo json_encode($response);
            return;
        }
    
        // Delete the appointment
        $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
        $stmt->bind_param("s", $appointment_id);
    
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Appointment deleted successfully';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error deleting appointment: ' . $conn->error;
        }
    
        echo json_encode($response);
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
    
        // Fetch user details based on user_id
        $stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
        if (!$stmt) {
            error_log("Error preparing query: " . $conn->error);
            $response['status'] = 'error';
            $response['message'] = 'Failed to prepare query.';
            echo json_encode($response);
            return;
        }
    
        $stmt->bind_param("s", $user_id);
        if (!$stmt->execute()) {
            error_log("Error executing query: " . $stmt->error);
            $response['status'] = 'error';
            $response['message'] = 'Failed to execute query.';
            echo json_encode($response);
            return;
        }
    
        $user_result = $stmt->get_result();
    
        if ($user_result->num_rows == 0) {
            $stmt->close();
            $response['status'] = 'error';
            $response['message'] = 'User not found';
            echo json_encode($response);
            return;
        }
    
        $user = $user_result->fetch_assoc();
        $stmt->close();
    
        // Fetch appointments based on user_id
        $stmt = $conn->prepare("SELECT a.appointment_id, a.user_id, a.doctor_id, a.appointment_time, d.contact_number, a.created_at, d.name, d.email 
                                FROM appointments a 
                                JOIN users d ON a.doctor_id = d.user_id
                                WHERE a.user_id = ? ORDER BY a.appointment_id DESC");
        if (!$stmt) {
            error_log("Error preparing query: " . $conn->error);
            $response['status'] = 'error';
            $response['message'] = 'Failed to prepare query.';
            echo json_encode($response);
            return;
        }
    
        $stmt->bind_param("s", $user_id);
        if (!$stmt->execute()) {
            error_log("Error executing query: " . $stmt->error);
            $response['status'] = 'error';
            $response['message'] = 'Failed to execute query.';
            echo json_encode($response);
            return;
        }
    
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $appointments = array();
    
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
    
            // Return success response with user details and appointments
            $response['status'] = 'success';
            $response['appointments'] = $appointments;
        } else {
            // If no appointments found for the user
            $response['status'] = 'error';
            $response['message'] = 'No appointments found for this user';
        }
    
        // Return the response
        echo json_encode($response);
        $stmt->close();
    }          

    private function isValidDateTime($datetime, $format = 'Y-m-d H:i:s') {
        $dt = DateTime::createFromFormat($format, $datetime);
        return $dt && $dt->format($format) === $datetime;
    }
}
?>