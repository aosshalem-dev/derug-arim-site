<?php
/**
 * Simple test endpoint to check API connectivity
 */

// Start output buffering
ob_start();

// Set headers
header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => true,
    'message' => 'API test successful',
    'checks' => []
];

// Check 1: PHP version
$response['checks']['php_version'] = PHP_VERSION;

// Check 2: Database config file
try {
    $db_config_path = __DIR__ . '/../../src/config/database.php';
    if (file_exists($db_config_path)) {
        $response['checks']['database_config_exists'] = true;
        require_once $db_config_path;
        $response['checks']['database_config_loaded'] = true;
    } else {
        $response['checks']['database_config_exists'] = false;
        $response['checks']['database_config_path'] = $db_config_path;
    }
} catch (Exception $e) {
    $response['checks']['database_config_error'] = $e->getMessage();
}

// Check 3: Database connection
try {
    if (function_exists('getDbConnection')) {
        $conn = getDbConnection();
        $response['checks']['database_connection'] = 'success';
        $response['checks']['database_host'] = DB_HOST ?? 'not defined';
        $response['checks']['database_name'] = DB_NAME ?? 'not defined';
        
        // Test query
        $test_query = "SELECT 1 as test";
        $result = $conn->query($test_query);
        if ($result) {
            $response['checks']['database_query'] = 'success';
        } else {
            $response['checks']['database_query'] = 'failed: ' . $conn->error;
        }
        
        closeDbConnection($conn);
    } else {
        $response['checks']['database_connection'] = 'getDbConnection function not found';
    }
} catch (Exception $e) {
    $response['checks']['database_connection'] = 'error: ' . $e->getMessage();
}

// Check 4: Config.php
$config_paths = [
    __DIR__ . '/../../config.php',
    __DIR__ . '/../../../config.php'
];
$response['checks']['config_paths_checked'] = $config_paths;
foreach ($config_paths as $path) {
    if (file_exists($path)) {
        $response['checks']['config_found'] = $path;
        break;
    }
}

// Clean output buffer and send response
ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
ob_end_flush();




