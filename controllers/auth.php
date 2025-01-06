<?php
class AuthController{
    public function register() {
        global $conn;
        date_default_timezone_set('Asia/Manila');
        $response = array();
    
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = bin2hex(random_bytes(16));
        $name = htmlspecialchars(isset($data['name']) ? $data['name'] : '');
        $email = htmlspecialchars(isset($data['email']) ? $data['email'] : '');
        $contact_number = htmlspecialchars(isset($data['contact_number']) ? $data['contact_number'] : '');
        $role = 'user';
        $password = htmlspecialchars(isset($data['password']) ? $data['password'] : '');
        $confirm_password = htmlspecialchars(isset($data['confirm_password']) ? $data['confirm_password'] : '');
        $created_at = date('Y-m-d H:i:s');
    
        if(empty($name)){
            $response['status'] = 'error';
            $response['message'] = 'Name cannot be empty';
            echo json_encode($response);
            return;
        }
    
        if(empty($email)){
            $response['status'] = 'error';
            $response['message'] = 'Email cannot be empty';
            echo json_encode($response);
            return;
        } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $response['status'] = 'error';
            $response['message'] = 'Invalid email format';
            echo json_encode($response);
            return;
        }
    
        if(empty($contact_number)){
            $response['status'] = 'error';
            $response['message'] = 'Contact number cannot be empty';
            echo json_encode($response);
            return;
        } else if(!preg_match('/^\d{10,15}$/', $contact_number)){
            $response['status'] = 'error';
            $response['message'] = 'Contact number must be between 10 and 15 digits';
            echo json_encode($response);
            return;
        }
    
        if(empty($password)){
            $response['status'] = 'error';
            $response['message'] = 'Password cannot be empty';
            echo json_encode($response);
            return;
        } else if(strlen($password) < 6){
            $response['status'] = 'error';
            $response['message'] = 'Password must be at least 6 characters long';
            echo json_encode($response);
            return;
        } else if(!preg_match('/[A-Z]/', $password)){
            $response['status'] = 'error';
            $response['message'] = 'Password must contain at least one uppercase letter';
            echo json_encode($response);
            return;
        } else if(!preg_match('/[a-z]/', $password)){
            $response['status'] = 'error';
            $response['message'] = 'Password must contain at least one lowercase letter';
            echo json_encode($response);
            return;
        } else if(!preg_match('/\d/', $password)){
            $response['status'] = 'error';
            $response['message'] = 'Password must contain at least one number';
            echo json_encode($response);
            return;
        }
    
        if(empty($confirm_password)){
            $response['status'] = 'error';
            $response['message'] = 'Confirm Password cannot be empty';
            echo json_encode($response);
            return;
        } else if($password != $confirm_password){
            $response['status'] = 'error';
            $response['message'] = 'Passwords do not match';
            echo json_encode($response);
            return;
        }
    
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            $response['status'] = 'error';
            $response['message'] = 'This user already exists';
            echo json_encode($response);
            return;
        }
    
        $stmt->close();
    
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (user_id, name, email, contact_number, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssss', $user_id, $name, $email, $contact_number, $hashed_password, $role, $created_at);
        
        if ($stmt->execute()){
            $response['status'] = 'success';
            $response['message'] = 'User created successfully';
            echo json_encode($response);
            return;
        } else{
            $response['status'] = 'error';
            $response['message'] = 'Error creating user: ' . $conn->error;
            echo json_encode($response);
            return;
        }
    }    

    public function login() {
        global $conn;
        date_default_timezone_set('Asia/Manila');
        $response = array();

        $data = json_decode(file_get_contents("php://input"), true);
        $email = htmlspecialchars(isset($data['email']) ? $data['email'] : '');
        $password = htmlspecialchars(isset($data['password']) ? $data['password'] : '');
        $created_at = date('Y-m-d H:i:s');

        if(empty($email)){
            $response['status'] = 'error';
            $response['message'] = 'Email cannot be empty';
            echo json_encode($response);
            return;
        } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $response['status'] = 'error';
            $response['message'] = 'Invalid email format';
            echo json_encode($response);
            return;
        }
        
        if(empty($password)){
            $response['status'] = 'error';
            $response['message'] = 'Password cannot be empty';
            echo json_encode($response);
            return;
        }

        // Check if user details are correct
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows === 0) {
            $response['status'] = 'error';
            $response['message'] = 'Email or password is incorrect';
            echo json_encode($response);
            return;
        }

        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['password'])) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid email or password.';
            echo json_encode($response);
            return;
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Login successful.';
            $response['user'] = [
                'user_id' => $user['user_id'],
                'name' => ucwords($user['name']),
                'email' => $user['email'],
                'role' => $user['role']
            ];
            echo json_encode($response);
            return;
        }
    }
}
?>