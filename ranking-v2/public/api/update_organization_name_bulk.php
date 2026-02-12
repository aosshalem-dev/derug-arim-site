<?php
/**
 * API endpoint to update organization name in all records
 * Updates all ranking_urls records with old_name to new_name
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
    
    // Get POST data
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!$input) {
        sendErrorResponse('נתונים לא תקינים');
    }
    
    $oldName = isset($input['old_name']) ? trim($input['old_name']) : '';
    $newName = isset($input['new_name']) ? trim($input['new_name']) : '';
    
    if (empty($oldName)) {
        sendErrorResponse('שם ישן הוא שדה חובה');
    }
    
    if (empty($newName)) {
        sendErrorResponse('שם חדש הוא שדה חובה');
    }
    
    if ($oldName === $newName) {
        sendResponse([
            'success' => true,
            'message' => 'השמות זהים - אין צורך בעדכון',
            'updated_count' => 0
        ]);
    }
    
    // Update all records with the old name to the new name
    $updateQuery = "UPDATE ranking_urls SET organization_name = ? WHERE organization_name = ?";
    $stmt = $conn->prepare($updateQuery);
    
    if (!$stmt) {
        closeDbConnection($conn);
        throw new Exception('שגיאה בהכנת שאילתת עדכון: ' . $conn->error);
    }
    
    $stmt->bind_param("ss", $newName, $oldName);
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        closeDbConnection($conn);
        throw new Exception('שגיאה בביצוע עדכון: ' . $error);
    }
    
    $updatedCount = $stmt->affected_rows;
    $stmt->close();
    closeDbConnection($conn);
    
    sendResponse([
        'success' => true,
        'message' => "עודכנו $updatedCount רשומות בהצלחה",
        'updated_count' => $updatedCount,
        'old_name' => $oldName,
        'new_name' => $newName
    ]);
    
} catch (Exception $e) {
    error_log("Error updating organization name bulk: " . $e->getMessage());
    sendErrorResponse('שגיאה בעדכון שם ארגון: ' . $e->getMessage());
}



