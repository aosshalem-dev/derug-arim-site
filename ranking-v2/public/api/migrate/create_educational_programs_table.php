<?php
/**
 * API endpoint to create educational_programs table
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
    $db_file = __DIR__ . '/../../../src/config/database.php';
    if (!file_exists($db_file)) {
        throw new Exception("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    // Load migration function
    $migration_file = __DIR__ . '/../../../src/utils/migrate_create_educational_programs_table.php';
    if (!file_exists($migration_file)) {
        throw new Exception("Migration file not found: $migration_file");
    }
    require_once $migration_file;
    
    // Run migration
    $result = createEducationalProgramsTable();
    
    sendResponse([
        'success' => true,
        'message' => $result['message'],
        'action' => $result['action']
    ]);
    
} catch (Exception $e) {
    error_log("Error creating educational_programs table: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ", Line: " . $e->getLine());
    sendErrorResponse('שגיאה ביצירת טבלת educational_programs: ' . $e->getMessage());
}



