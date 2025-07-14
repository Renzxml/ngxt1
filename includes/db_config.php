<?php
// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('Asia/Manila');

// Detect if running on localhost
$is_localhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) || str_contains(php_uname(), 'Windows');

// Declare class if not already declared
if (!class_exists('db_connect')) {
    class db_connect {
        public $conn;
        public $error;

        public function connect() {
            global $is_localhost;

            // Localhost credentials
            if ($is_localhost) {
                $host = 'localhost';
                $user = 'root';
                $pass = '';
                $name = 'ngxt_db';
            } else {
                // Hosting credentials
                $host = 'localhost'; // Or actual DB host from your provider if different
                $user = 'oubomnof_ngxt';
                $pass = 'ngxt_pass';
                $name = 'oubomnof_ngxt_db';
            }

            $this->conn = @new mysqli($host, $user, $pass, $name);

            if ($this->conn->connect_error) {
                $this->error = "DB Connection failed: " . $this->conn->connect_error;
                return false;
            }

            $this->conn->query("SET time_zone = '+08:00'");
            return true;
        }
    }
}
?>
