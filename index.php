<?php
// CORS and Header settings
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Allow only POST method for storage operations
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => false, 'message' => 'Only POST method is allowed']);
    exit();
}

// Read .env file logic
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// Validate API Key securely
$secretkey = $_ENV['API_KEY'] ?? '4566545';
$headers = function_exists('getallheaders') ? getallheaders() : [];
$providedKey = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? null;

if ($secretkey !== "" && (!is_string($providedKey) || !hash_equals($secretkey, $providedKey))) {
    http_response_code(401);
    echo json_encode(["status" => false, 'message' => 'Unauthorized: Invalid API Key']);
    exit();
}

// Parse request body if sent as JSON
$jsonInput = [];
$rawInput = file_get_contents('php://input');
if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $jsonInput = $decoded;
    }
}

// Determine action (upload, delete, replace)
$action = $_POST['action'] ?? $jsonInput['action'] ?? $_GET['action'] ?? null;
if (!$action) {
    if (isset($_FILES['file'])) {
        $action = 'upload';
    } else {
        http_response_code(400);
        echo json_encode(["status" => false, 'message' => 'Action parameter is required (upload, delete, replace)']);
        exit();
    }
}
$action = strtolower(trim($action));

$uploadDir = __DIR__ . '/storage/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Helper function to build full public URL
function buildFileUrl($filename) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $protocol . $host . $scriptDir . '/storage/' . $filename;
}

// Helper function for file upload validation & saving
function processFileUpload($file, $uploadDir) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'No file uploaded or upload error occurred';
        if (isset($file['error'])) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg = 'File size exceeds maximum upload limit set in php.ini';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg = 'No file selected for upload';
                    break;
            }
        }
        return ["status" => false, "message" => $errorMsg, "code" => 400];
    }

    $maxSize = 8 * 1024 * 1024; // 8MB limit
    if ($file['size'] > $maxSize) {
        return ["status" => false, "message" => "File size exceeds the maximum limit of 8MB", "code" => 400];
    }

    $originalName = basename($file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'docx', 'xlsx', 'pptx', 'txt', 'zip', 'mp4', 'mp3'];

    if (!in_array($ext, $allowedExtensions)) {
        return ["status" => false, "message" => "File type (." . $ext . ") not allowed", "code" => 400];
    }

    $uniqueName = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            "status" => true,
            "data" => [
                "original_name" => $originalName,
                "saved_name" => $uniqueName,
                "size" => $file['size'],
                "url" => buildFileUrl($uniqueName)
            ]
        ];
    } else {
        return ["status" => false, "message" => "Failed to save file on server", "code" => 500];
    }
}

// Helper function to safely extract basename and delete file
function deleteStorageFile($fileInput, $uploadDir) {
    if (empty($fileInput)) {
        return ["status" => false, "message" => "Target file name or URL is required for deletion", "code" => 400];
    }

    // Extract filename securely using parse_url + basename to prevent path traversal
    $parsedPath = parse_url($fileInput, PHP_URL_PATH);
    $filename = basename($parsedPath);

    if (empty($filename) || $filename === '.' || $filename === '..') {
        return ["status" => false, "message" => "Invalid file name", "code" => 400];
    }

    $targetPath = $uploadDir . $filename;

    if (file_exists($targetPath) && is_file($targetPath)) {
        if (unlink($targetPath)) {
            return ["status" => true, "message" => "File deleted successfully", "deleted_file" => $filename];
        } else {
            return ["status" => false, "message" => "Failed to delete file from server", "code" => 500];
        }
    } else {
        return ["status" => false, "message" => "File not found: " . $filename, "code" => 404];
    }
}

// Handle Operations based on action
switch ($action) {
    case 'upload':
        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "No file provided for upload"]);
            exit();
        }
        $result = processFileUpload($_FILES['file'], $uploadDir);
        if ($result['status']) {
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "File uploaded successfully",
                "data" => $result['data']
            ], JSON_UNESCAPED_SLASHES);
        } else {
            http_response_code($result['code']);
            echo json_encode(["status" => false, "message" => $result['message']]);
        }
        break;

    case 'delete':
        $targetFile = $_POST['file_name'] ?? $_POST['file_url'] ?? $_POST['old_file'] ?? $jsonInput['file_name'] ?? $jsonInput['file_url'] ?? $jsonInput['old_file'] ?? null;
        $result = deleteStorageFile($targetFile, $uploadDir);
        if ($result['status']) {
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => $result['message'],
                "deleted_file" => $result['deleted_file']
            ]);
        } else {
            http_response_code($result['code']);
            echo json_encode(["status" => false, "message" => $result['message']]);
        }
        break;

    case 'replace':
        $oldFile = $_POST['old_file'] ?? $_POST['file_name'] ?? $_POST['file_url'] ?? $jsonInput['old_file'] ?? $jsonInput['file_name'] ?? $jsonInput['file_url'] ?? null;
        if (empty($oldFile)) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "old_file parameter is required for replace operation"]);
            exit();
        }
        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(["status" => false, "message" => "New file is required for replace operation"]);
            exit();
        }

        // 1. Delete old file if exists
        $deleteResult = deleteStorageFile($oldFile, $uploadDir);

        // 2. Upload new file
        $uploadResult = processFileUpload($_FILES['file'], $uploadDir);
        if ($uploadResult['status']) {
            http_response_code(200);
            echo json_encode([
                "status" => true,
                "message" => "File replaced successfully",
                "old_file_deleted" => $deleteResult['status'],
                "data" => $uploadResult['data']
            ], JSON_UNESCAPED_SLASHES);
        } else {
            http_response_code($uploadResult['code']);
            echo json_encode(["status" => false, "message" => $uploadResult['message']]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "Invalid action. Supported actions: upload, delete, replace"]);
        break;
}