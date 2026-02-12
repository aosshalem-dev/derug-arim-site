<?php
require_once(__DIR__ . '/../db/connection.php');

class Edge {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDB();
    }
    
    /**
     * יצירת קשר חדש
     */
    public function create($fromNodeId, $toNodeId, $relType, $data = [], $userId = null) {
        // שמירת גרסה אם זה עדכון
        if (isset($data['id']) && $data['id']) {
            $this->saveVersion($data['id'], $userId);
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO edges (from_node_id, to_node_id, rel_type, confidence, start_date, end_date, props, created_by, updated_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $props = isset($data['props']) ? json_encode($data['props']) : null;
        
        $stmt->execute([
            $fromNodeId,
            $toNodeId,
            $relType,
            $data['confidence'] ?? 'medium',
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $props,
            $userId,
            $userId
        ]);
        
        $edgeId = $this->pdo->lastInsertId();
        
        // רישום ב-audit log
        $edgeData = array_merge($data, [
            'from_node_id' => $fromNodeId,
            'to_node_id' => $toNodeId,
            'rel_type' => $relType
        ]);
        $this->logAction('CREATE', 'edge', $edgeId, null, $edgeData, $userId);
        
        return $edgeId;
    }
    
    /**
     * עדכון קשר
     */
    public function update($id, $data, $userId = null) {
        // שמירת גרסה קודמת
        $this->saveVersion($id, $userId);
        
        $oldData = $this->getById($id);
        
        $stmt = $this->pdo->prepare("
            UPDATE edges 
            SET from_node_id = ?, to_node_id = ?, rel_type = ?, confidence = ?, 
                start_date = ?, end_date = ?, props = ?, updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $props = isset($data['props']) ? json_encode($data['props']) : null;
        
        $stmt->execute([
            $data['from_node_id'] ?? $oldData['from_node_id'],
            $data['to_node_id'] ?? $oldData['to_node_id'],
            $data['rel_type'] ?? $oldData['rel_type'],
            $data['confidence'] ?? $oldData['confidence'],
            $data['start_date'] ?? $oldData['start_date'],
            $data['end_date'] ?? $oldData['end_date'],
            $props,
            $userId,
            $id
        ]);
        
        // רישום ב-audit log
        $newData = $this->getById($id);
        $this->logAction('UPDATE', 'edge', $id, $oldData, $newData, $userId);
        
        return true;
    }
    
    /**
     * קבלת קשר לפי ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, 
                   n1.label as from_label, n1.type as from_type,
                   n2.label as to_label, n2.type as to_type
            FROM edges e
            JOIN nodes n1 ON e.from_node_id = n1.id
            JOIN nodes n2 ON e.to_node_id = n2.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $edge = $stmt->fetch();
        
        if ($edge) {
            $edge['props'] = $edge['props'] ? json_decode($edge['props'], true) : [];
        }
        
        return $edge;
    }
    
    /**
     * קבלת כל הקשרים של צומת
     */
    public function getByNode($nodeId) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, 
                   n1.label as from_label, n1.type as from_type,
                   n2.label as to_label, n2.type as to_type
            FROM edges e
            JOIN nodes n1 ON e.from_node_id = n1.id
            JOIN nodes n2 ON e.to_node_id = n2.id
            WHERE e.from_node_id = ? OR e.to_node_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$nodeId, $nodeId]);
        $edges = $stmt->fetchAll();
        
        foreach ($edges as &$edge) {
            $edge['props'] = $edge['props'] ? json_decode($edge['props'], true) : [];
        }
        
        return $edges;
    }
    
    /**
     * מציאת מסלול בין שני צמתים (BFS פשוט)
     */
    public function getPath($fromId, $toId, $maxDepth = 5) {
        // זה דורש אלגוריתם BFS - נשתמש ב-PHP
        $visited = [];
        $queue = [[$fromId, []]];
        
        while (!empty($queue) && count($queue[0][1]) < $maxDepth) {
            [$currentId, $path] = array_shift($queue);
            
            if ($currentId == $toId) {
                return $path;
            }
            
            if (isset($visited[$currentId])) continue;
            $visited[$currentId] = true;
            
            // מציאת כל הקשרים מהצומת הנוכחי
            $stmt = $this->pdo->prepare("
                SELECT to_node_id, from_node_id, id, rel_type
                FROM edges
                WHERE from_node_id = ? OR to_node_id = ?
            ");
            $stmt->execute([$currentId, $currentId]);
            $edges = $stmt->fetchAll();
            
            foreach ($edges as $edge) {
                $nextId = $edge['to_node_id'] == $currentId ? $edge['from_node_id'] : $edge['to_node_id'];
                if (!isset($visited[$nextId])) {
                    $newPath = array_merge($path, [$edge['id']]);
                    $queue[] = [$nextId, $newPath];
                }
            }
        }
        
        return null; // לא נמצא מסלול
    }
    
    /**
     * מחיקת קשר
     */
    public function delete($id, $userId = null) {
        $oldData = $this->getById($id);
        
        $stmt = $this->pdo->prepare("DELETE FROM edges WHERE id = ?");
        $stmt->execute([$id]);
        
        // רישום ב-audit log
        $this->logAction('DELETE', 'edge', $id, $oldData, null, $userId);
        
        return true;
    }
    
    /**
     * שמירת גרסה קודמת
     */
    private function saveVersion($edgeId, $userId = null) {
        $edge = $this->getById($edgeId);
        if (!$edge) return;
        
        // קבלת מספר גרסה הבא
        $versionStmt = $this->pdo->prepare("SELECT MAX(version_number) as max_version FROM edge_versions WHERE edge_id = ?");
        $versionStmt->execute([$edgeId]);
        $versionData = $versionStmt->fetch();
        $nextVersion = ($versionData['max_version'] ?? 0) + 1;
        
        // שמירת גרסה
        $stmt = $this->pdo->prepare("
            INSERT INTO edge_versions (edge_id, version_number, from_node_id, to_node_id, rel_type, confidence, start_date, end_date, props, changed_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $edgeId,
            $nextVersion,
            $edge['from_node_id'],
            $edge['to_node_id'],
            $edge['rel_type'],
            $edge['confidence'],
            $edge['start_date'],
            $edge['end_date'],
            json_encode($edge['props']),
            $userId
        ]);
    }
    
    /**
     * קבלת גרסאות קודמות
     */
    public function getVersions($edgeId) {
        $stmt = $this->pdo->prepare("
            SELECT ev.*, u.email as changed_by_email
            FROM edge_versions ev
            LEFT JOIN users u ON ev.changed_by = u.id
            WHERE ev.edge_id = ?
            ORDER BY ev.version_number DESC
        ");
        $stmt->execute([$edgeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * רישום ב-audit log
     */
    private function logAction($actionType, $entityType, $entityId, $oldData, $newData, $userId = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO audit_log (user_id, action_type, entity_type, entity_id, old_data, new_data, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $actionType,
            $entityType,
            $entityId,
            $oldData ? json_encode($oldData) : null,
            $newData ? json_encode($newData) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}
?>

