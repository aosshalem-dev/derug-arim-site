<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../auth/check.php');
require_once(__DIR__ . '/../models/Edge.php');

$method = $_SERVER['REQUEST_METHOD'];
$edge = new Edge();
$userId = getCurrentUserId();

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // קבלת קשר ספציפי
            $result = $edge->getById($_GET['id']);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } elseif (isset($_GET['node_id'])) {
            // כל הקשרים של צומת
            $result = $edge->getByNode($_GET['node_id']);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } elseif (isset($_GET['path'])) {
            // מציאת מסלול
            $path = explode(',', $_GET['path']);
            if (count($path) == 2) {
                $result = $edge->getPath((int)$path[0], (int)$path[1]);
                echo json_encode(['path' => $result], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid path format'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parameters'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        
        if (!isset($data['from_node_id']) || !isset($data['to_node_id']) || !isset($data['rel_type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        $edgeId = $edge->create(
            $data['from_node_id'],
            $data['to_node_id'],
            $data['rel_type'],
            $data,
            $userId
        );
        echo json_encode(['success' => true, 'id' => $edgeId], JSON_UNESCAPED_UNICODE);
        break;
        
    case 'PUT':
        if (isset($_GET['id'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                parse_str(file_get_contents('php://input'), $data);
            }
            $edge->update($_GET['id'], $data, $userId);
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id parameter'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            $edge->delete($_GET['id'], $userId);
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

