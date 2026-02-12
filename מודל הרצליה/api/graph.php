<?php
// חשוב: header לפני כל output
header('Content-Type: application/json; charset=utf-8');

// טיפול בשגיאות - לא להציג HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once(__DIR__ . '/../auth/check.php');
    require_once(__DIR__ . '/../db/connection.php');
    require_once(__DIR__ . '/../models/Node.php');
    require_once(__DIR__ . '/../models/Edge.php');
    
    $pdo = getDB();
    $nodeModel = new Node();
    $edgeModel = new Edge();
    
    // פרמטרים
    $nodeId = $_GET['node_id'] ?? null;
    $depth = (int)($_GET['depth'] ?? 2);
    $type = $_GET['type'] ?? null;
    
    // אם יש node_id, טוענים את הצומת וכל הקשרים שלו
    if ($nodeId) {
        $centerNode = $nodeModel->getById($nodeId);
        if (!$centerNode) {
            http_response_code(404);
            echo json_encode(['error' => 'Node not found'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // איסוף כל הצמתים הקשורים (עד עומק מסוים)
        $nodes = [$centerNode];
        $edges = [];
        $visited = [$nodeId => true];
        $queue = [[$nodeId, 0]];
        
        while (!empty($queue)) {
            [$currentId, $currentDepth] = array_shift($queue);
            
            if ($currentDepth >= $depth) continue;
            
            $nodeEdges = $edgeModel->getByNode($currentId);
            foreach ($nodeEdges as $edge) {
                $edges[] = $edge;
                
                $nextId = $edge['from_node_id'] == $currentId ? $edge['to_node_id'] : $edge['from_node_id'];
                
                if (!isset($visited[$nextId])) {
                    $visited[$nextId] = true;
                    $nextNode = $nodeModel->getById($nextId);
                    if ($nextNode) {
                        $nodes[] = $nextNode;
                        $queue[] = [$nextId, $currentDepth + 1];
                    }
                }
            }
        }
    } else {
        // טעינת כל הצמתים והקשרים (או לפי סוג)
        if ($type) {
            $nodes = $nodeModel->getByType($type);
        } else {
            $nodes = $nodeModel->search('');
        }
        
        // טעינת כל הקשרים
        $nodeIds = array_column($nodes, 'id');
        if (!empty($nodeIds)) {
            $placeholders = implode(',', array_fill(0, count($nodeIds), '?'));
            $stmt = $pdo->prepare("
                SELECT e.*, 
                       n1.label as from_label, n1.type as from_type,
                       n2.label as to_label, n2.type as to_type
                FROM edges e
                JOIN nodes n1 ON e.from_node_id = n1.id
                JOIN nodes n2 ON e.to_node_id = n2.id
                WHERE e.from_node_id IN ($placeholders) OR e.to_node_id IN ($placeholders)
            ");
            $stmt->execute(array_merge($nodeIds, $nodeIds));
            $edges = $stmt->fetchAll();
            
            foreach ($edges as &$edge) {
                $edge['props'] = $edge['props'] ? json_decode($edge['props'], true) : [];
            }
        } else {
            $edges = [];
        }
    }
    
    // פורמט ל-Cytoscape.js
    $cyNodes = [];
    $cyEdges = [];
    
    foreach ($nodes as $n) {
        $color = '#666';
        if (in_array('problematic', $n['flags'] ?? [])) {
            $color = '#d00';
        } elseif (in_array('suspicious', $n['flags'] ?? [])) {
            $color = '#f80';
        }
        
        $cyNodes[] = [
            'data' => [
                'id' => (string)$n['id'],
                'label' => $n['label'],
                'type' => $n['type'],
                'flags' => $n['flags'] ?? []
            ],
            'style' => [
                'background-color' => $color
            ]
        ];
    }
    
    foreach ($edges as $e) {
        $cyEdges[] = [
            'data' => [
                'id' => (string)$e['id'],
                'source' => (string)$e['from_node_id'],
                'target' => (string)$e['to_node_id'],
                'label' => $e['rel_type'],
                'rel_type' => $e['rel_type'],
                'confidence' => $e['confidence']
            ]
        ];
    }
    
    echo json_encode([
        'nodes' => $cyNodes,
        'edges' => $cyEdges
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
