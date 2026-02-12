<?php
/**
 * API endpoint to clear all data from organizations table
 * WARNING: This deletes all records from the organizations table
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
    
    // First, get count before deletion
    $countQuery = "SELECT COUNT(*) as total FROM organizations";
    $countResult = $conn->query($countQuery);
    $countRow = $countResult->fetch_assoc();
    $totalBefore = (int)($countRow['total'] ?? 0);
    
    // Delete all records from organizations table
    // Note: This will not delete the table structure, only the data
    $deleteQuery = "DELETE FROM organizations";
    $result = $conn->query($deleteQuery);
    
    if (!$result) {
        closeDbConnection($conn);
        throw new Exception('שגיאה במחיקת נתונים: ' . $conn->error);
    }
    
    $deletedCount = $conn->affected_rows;
    closeDbConnection($conn);
    
    sendResponse([
        'success' => true,
        'message' => "נמחקו $deletedCount רשומות מטבלת organizations",
        'deleted_count' => $deletedCount
    ]);
    
} catch (Exception $e) {
    error_log("Error clearing organizations table: " . $e->getMessage());
    sendErrorResponse('שגיאה במחיקת נתונים: ' . $e->getMessage());
}



