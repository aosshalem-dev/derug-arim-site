<?php
/**
 * API endpoint to improve organization names in Hebrew using AI
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
    
    // Load utility function
    $util_file = __DIR__ . '/../../src/utils/improve_organization_names_hebrew.php';
    if (!file_exists($util_file)) {
        throw new Exception("Utility file not found: $util_file");
    }
    require_once $util_file;
    
    // Get parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    $only_non_hebrew = !isset($_GET['all']) || $_GET['all'] != '1';
    
    // Run improvement
    try {
        $stats = improveOrganizationNamesHebrew($limit, $only_non_hebrew);
        
        // Add detailed error info if there are errors
        $response = [
            'success' => true,
            'message' => 'שיפור שמות ארגונים בעברית הושלם',
            'stats' => $stats
        ];
        
        if ($stats['errors'] > 0) {
            $response['warning'] = "נמצאו {$stats['errors']} שגיאות. בדוק את error_log לפרטים.";
            // Try to get first few errors from log if possible
            error_log("Summary: {$stats['errors']} errors out of {$stats['processed']} processed records");
        }
        
        sendResponse($response);
    } catch (Exception $e) {
        error_log("Error in improveOrganizationNamesHebrew: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendErrorResponse('שגיאה בשיפור שמות ארגונים בעברית: ' . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error improving organization names Hebrew: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ", Line: " . $e->getLine());
    sendErrorResponse('שגיאה בשיפור שמות ארגונים בעברית: ' . $e->getMessage());
}

