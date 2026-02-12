<?php
/**
 * API endpoint to add organization names from ranking_urls to organizations table
 * Takes all unique organization names and adds/updates them in organizations table
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
    $util_file = __DIR__ . '/../../src/utils/populate_organizations_table.php';
    if (!file_exists($util_file)) {
        throw new Exception("Utility file not found: $util_file");
    }
    require_once $util_file;
    
    // Run population
    $stats = populateOrganizationsTable();
    
    sendResponse([
        'success' => true,
        'message' => 'שמות ארגונים נוספו/עודכנו בטבלת organizations בהצלחה',
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Error adding org names to organizations: " . $e->getMessage());
    sendErrorResponse('שגיאה בהוספת שמות לטבלת organizations: ' . $e->getMessage());
}



