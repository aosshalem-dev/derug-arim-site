<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../auth/check.php');
require_once(__DIR__ . '/../models/Node.php');

$method = $_SERVER['REQUEST_METHOD'];
$node = new Node();
$userId = getCurrentUserId();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // קבלת צומת ספציפי
            $result = $node->getById($_GET['id']);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } elseif (isset($_GET['type'])) {
            // קבלת צמתים לפי סוג
            $result = $node->getByType($_GET['type']);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } elseif (isset($_GET['search'])) {
            // חיפוש
            $result = $node->search(
                $_GET['search'] ?? '',
                $_GET['type'] ?? null,
                isset($_GET['flags']) ? json_decode($_GET['flags'], true) : null
            );
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            // כל הצמתים
            $result = $node->search('');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        
        $nodeId = $node->create($data, $userId);
        echo json_encode(['success' => true, 'id' => $nodeId], JSON_UNESCAPED_UNICODE);
        break;
        
    case 'PUT':
        parse_str(file_get_contents('php://input'), $data);
        if (isset($_GET['id'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                parse_str(file_get_contents('php://input'), $data);
            }
            $node->update($_GET['id'], $data, $userId);
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id parameter'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            $node->delete($_GET['id'], $userId);
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id parameter'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
}
?>

