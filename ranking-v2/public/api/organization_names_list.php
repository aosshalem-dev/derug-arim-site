<?php
/**
 * API endpoint to get list of all unique organization names from ranking_urls
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');

function sendResponse($data) {
    ob_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    ob_end_flush();
    exit;
}

function sendErrorResponse($message, $code = 500) {
    http_response_code($code);
    sendResponse([
        'success' => false,
        'error' => $message
    ]);
}

try {
    // Load database config
    $db_file = __DIR__ . '/../../src/config/database.php';
    if (!file_exists($db_file)) {
        throw new Exception("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    $conn = getDbConnection();
    
    // Get unique organization names with count
    $query = "SELECT 
                organization_name,
                COUNT(*) as url_count,
                GROUP_CONCAT(DISTINCT organization_type) as types
              FROM ranking_urls 
              WHERE organization_name IS NOT NULL 
                AND organization_name != ''
                AND organization_name != 'NULL'
              GROUP BY organization_name
              ORDER BY organization_name ASC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        closeDbConnection($conn);
        throw new Exception('שגיאה בשאילתת organization names: ' . $conn->error);
    }
    
    $names = [];
    while ($row = $result->fetch_assoc()) {
        $names[] = [
            'name' => $row['organization_name'],
            'url_count' => (int)$row['url_count'],
            'types' => $row['types'] ? explode(',', $row['types']) : []
        ];
    }
    
    closeDbConnection($conn);
    
    sendResponse([
        'success' => true,
        'names' => $names,
        'total' => count($names)
    ]);
    
} catch (Exception $e) {
    error_log("Error in organization_names_list API: " . $e->getMessage());
    sendErrorResponse('שגיאה בטעינת שמות ארגונים: ' . $e->getMessage());
}



