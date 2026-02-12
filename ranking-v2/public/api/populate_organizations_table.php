<?php
/**
 * API endpoint to populate organizations table from ranking_urls
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
    
    // Load populate function
    $populate_file = __DIR__ . '/../../src/utils/populate_organizations_table.php';
    if (!file_exists($populate_file)) {
        throw new Exception("Populate file not found: $populate_file");
    }
    require_once $populate_file;
    
    // Run population
    $stats = populateOrganizationsTable();
    
    sendResponse([
        'success' => true,
        'message' => "אוכלסה טבלת organizations בהצלחה",
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Error populating organizations table: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ", Line: " . $e->getLine());
    sendErrorResponse('שגיאה באוכלס טבלת organizations: ' . $e->getMessage());
}



