<?php
/**
 * API endpoint for deleting a record
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'מזהה רשומה נדרש']);
    exit;
}

$id = (int)$input['id'];
$conn = getDbConnection();

$query = "DELETE FROM ranking_urls WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'רשומה נמחקה בהצלחה'
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'שגיאה במחיקת הרשומה: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
closeDbConnection($conn);

