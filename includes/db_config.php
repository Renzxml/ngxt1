<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevent multiple session starts
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Define DB constants only if not already defined
if (!defined('db_host')) define('db_host', 'localhost');
if (!defined('db_user')) define('db_user', 'oubomnof_ngxt');
if (!defined('db_pass')) define('db_pass', 'ngxt_pass');
if (!defined('db_name')) define('db_name', 'oubomnof_ngxt_db');


// Declare class only if it doesn't already exist
if (!class_exists('db_connect')) {
    class db_connect {
        public $host = db_host;
        public $user = db_user;
        public $pass = db_pass;
        public $name = db_name;
        public $conn;
        public $error;

        public function connect() {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);

            // Set timezone for MySQL session
            mysqli_query($this->conn, "SET time_zone = '+08:00'");

            if (!$this->conn || $this->conn->connect_error) {
                $this->error = "Fatal Error: Can't connect to database. " . $this->conn->connect_error;
                return false;
            }

            return true;
        }
    }
}

?>
