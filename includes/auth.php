<?php

class auth {
    private $conn;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    // Register user
    public function register($fName, $lName, $email, $password, $cPassword) {
        // Check password match
        if ($password !== $cPassword) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }

        // Check if email exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }
        $stmt->close();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $this->conn->prepare("INSERT INTO users (fName, lName, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fName, $lName, $email, $hashedPassword);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Registration successful.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Registration failed, try again.'];
        }
    }

    // Login user
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, fName, lName, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fName'] = $user['fName'];
                $_SESSION['lName'] = $user['lName'];

                $stmt->close();
                return ['success' => true, 'message' => 'Login successful.'];
            }
        }
        $stmt->close();
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    // Logout user
    public function logout() {
        session_unset();
        session_destroy();
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?>
