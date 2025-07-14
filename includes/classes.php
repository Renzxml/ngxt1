<?php

session_start(); // Start session at the top of the script
include 'db_config.php';

require_once __DIR__ . '/../vendor/autoload.php'; // Include Cloudinary SDK

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent redeclaration of db_class
if (!class_exists('db_class')) {

    class db_class extends db_connect {

        private $baseURL = "http://localhost/ngxt";

        public function __construct(){
            $this->connect();
            $this->initCloudinary();
        }

        private function initCloudinary() {
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => 'dlrhffijd',
                    'api_key'    => '927768898319942',
                    'api_secret' => 'n2o3pJSA-P72T1irAZCXWvkg1oA'
                ],
                'url' => ['secure' => true]
            ]);
        }

        public function uploadToCloudinary($filePath) {
            try {
                $response = (new UploadApi())->upload($filePath, [
                    'resource_type' => 'auto'
                ]);

                return [
                    'success' => true,
                    'url' => $response['secure_url'],
                    'type' => $response['resource_type'],
                    'raw' => $response
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        public function getCompanyDetails() {
            $sql = "SELECT * FROM company_details_tbl LIMIT 1";
            $result = $this->conn->query($sql);

            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            } else {
                return null;
            }
        }
    }

}
?>
