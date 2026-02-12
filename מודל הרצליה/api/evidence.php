<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../auth/check.php');
require_once(__DIR__ . '/../models/Evidence.php');

$method = $_SERVER['REQUEST_METHOD'];
$evidence = new Evidence();
$userId = getCurrentUserId();

switch ($method) {
    case 'GET':
        if (isset($_GET['edge_id'])) {
            $result = $evidence->getByEdge($_GET['edge_id']);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } elseif (isset($_GET['id'])) {
            $result = $evidence->getById($_GET['id']);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing edge_id or id parameter'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        
        if (!isset($data['edge_id']) || !isset($data['source_type']) || !isset($data['source_ref'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        $evidenceId = $evidence->addToEdge($data['edge_id'], $data, $userId);
        echo json_encode(['success' => true, 'id' => $evidenceId], JSON_UNESCAPED_UNICODE);
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            $evidence->delete($_GET['id'], $userId);
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

