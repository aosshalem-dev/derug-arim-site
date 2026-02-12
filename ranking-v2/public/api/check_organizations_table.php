<?php
/**
 * API endpoint to check if organizations table exists
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

try {
    // Load database config
    $db_file = __DIR__ . '/../../src/config/database.php';
    if (!file_exists($db_file)) {
        throw new Exception("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    $conn = getDbConnection();
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'organizations'");
    $table_exists = $result && $result->num_rows > 0;
    
    $info = [
        'database_name' => DB_NAME,
        'table_exists' => $table_exists
    ];
    
    if ($table_exists) {
        // Get table structure
        $columns_result = $conn->query("SHOW COLUMNS FROM organizations");
        $columns = [];
        while ($row = $columns_result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        // Get row count
        $count_result = $conn->query("SELECT COUNT(*) as count FROM organizations");
        $count_row = $count_result->fetch_assoc();
        
        $info['columns'] = $columns;
        $info['row_count'] = (int)$count_row['count'];
    }
    
    // List all tables in database
    $tables_result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $tables_result->fetch_assoc()) {
        $tables[] = array_values($row)[0];
    }
    $info['all_tables'] = $tables;
    
    closeDbConnection($conn);
    
    sendResponse([
        'success' => true,
        'info' => $info
    ]);
    
} catch (Exception $e) {
    error_log("Error checking organizations table: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}



