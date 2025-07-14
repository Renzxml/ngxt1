<?php

session_start(); // Start session at the top of the script
include 'db_config.php';

require_once __DIR__ . '/../vendor/autoload.php'; // Include Cloudinary SDK

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!class_exists('db_class')) {

    class db_class extends db_connect {

        private $baseURL = "http://localhost/ngxt";

        public function __construct() {
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

        /**
         * Upload file to Cloudinary
         * @param string $filePath Local path to the file
         * @param string $category Determines folder: image, video, profile, archived, chunks
         * @return array Response with success, URL, etc.
         */
        public function uploadToCloudinary($filePath, $category = 'image') {
            $folder = 'uploads/Gallery/Images'; // Default folder

            switch (strtolower($category)) {
                case 'video':
                    $folder = 'uploads/Gallery/Videos';
                    break;
                case 'profile':
                    $folder = 'uploads/Profiles';
                    break;
                case 'archived':
                    $folder = 'uploads/Gallery/Archived';
                    break;
                case 'chunks':
                    $folder = 'uploads/Gallery/Chunks';
                    break;
                case 'cover_photo':
                    $folder = 'uploads/Gallery/cover_photos';
                    break;
                case 'profile_photo':
                    $folder = 'uploads/Gallery/profile_photos';
                    break;
                case 'profile_image':
                    $folder = 'uploads/Gallery/profile_images';
                    break;
                case 'services_image':
                    $folder = 'uploads/Gallery/services_images';
                    break;
                case 'image':
                default:
                    $folder = 'uploads/Gallery/Images';
                    break;
            }

            try {
                $uploadApi = new UploadApi();
                $response = $uploadApi->upload($filePath, [
                    'resource_type' => $category === 'video' ? 'video' : 'auto',
                    'folder' => $folder
                ]);

                return [
                    'success' => true,
                    'url' => $response['secure_url'],
                    'type' => $response['resource_type'],
                    'cloudinary_public_id' => $response['public_id'],
                    'raw' => $response
                ];

            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }


        public function moveToCloudinaryArchive($publicId, $archiveFolder, $resourceType = 'image') {
            try {
                $uploadApi = new UploadApi();

                // Remove .mp4/.jpg extension from the public ID if it's there
                $publicId = preg_replace('/\.[a-zA-Z0-9]+$/', '', $publicId);

                $newPublicId = $archiveFolder . '/' . basename($publicId);

                // Sanitize resource type
                $resourceType = in_array($resourceType, ['image', 'video', 'raw']) ? $resourceType : 'auto';

                $result = $uploadApi->rename($publicId, $newPublicId, [
                    'resource_type' => $resourceType,
                    'overwrite' => true
                ]);

                return [
                    'success' => true,
                    'new_url' => $result['secure_url'],
                    'new_public_id' => $result['public_id']
                ];

            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }


        public function deleteFromCloudinary($publicId, $resourceType = 'image') {
            try {
                $uploadApi = new \Cloudinary\Api\Upload\UploadApi();

                // Strip file extension if it exists (Cloudinary public IDs shouldn't have it)
                $publicId = preg_replace('/\.[a-zA-Z0-9]+$/', '', $publicId);

                $uploadApi->destroy($publicId, [
                    'resource_type' => $resourceType
                ]);

                return [
                    'success' => true
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
