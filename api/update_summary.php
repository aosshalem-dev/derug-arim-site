<?php
/**
 * API endpoint for updating summary
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';
require_once '../ensure_metadata_columns.php';

// Ensure all columns exist (including short_summary)
ensureMetadataColumns();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'מזהה רשומה נדרש'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($input['summary'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'סיכום נדרש'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = (int)$input['id'];
$summary = trim($input['summary']);

if (empty($summary)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'סיכום לא יכול להיות ריק'], JSON_UNESCAPED_UNICODE);
    exit;
}

$conn = getDbConnection();

try {
    // Check if record exists
    $checkQuery = "SELECT id FROM ranking_urls WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if (!$result->fetch_assoc()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'רשומה לא נמצאה'], JSON_UNESCAPED_UNICODE);
        $checkStmt->close();
        closeDbConnection($conn);
        exit;
    }
    
    $checkStmt->close();
    
    // Update summary
    $updateQuery = "UPDATE ranking_urls SET short_summary = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    
    if (!$updateStmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    $updateStmt->bind_param("si", $summary, $id);
    
    if (!$updateStmt->execute()) {
        throw new Exception("Failed to execute update: " . $updateStmt->error);
    }
    
    $updateStmt->close();
    closeDbConnection($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'סיכום עודכן בהצלחה',
        'summary' => $summary
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'שגיאה בעדכון הסיכום: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
    if (isset($updateStmt)) {
        $updateStmt->close();
    }
    if (isset($conn)) {
        closeDbConnection($conn);
    }
}

