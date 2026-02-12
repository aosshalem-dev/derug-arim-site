<?php
require_once(__DIR__ . '/../db/connection.php');

class Node {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDB();
    }
    
    /**
     * יצירת צומת חדש
     */
    public function create($data, $userId = null) {
        // שמירת גרסה לפני יצירה (אם יש node_id קיים - עדכון)
        if (isset($data['id']) && $data['id']) {
            $this->saveVersion($data['id'], $userId);
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO nodes (type, label, description, flags, props, canonical_key, created_by, updated_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $flags = isset($data['flags']) ? json_encode($data['flags']) : null;
        $props = isset($data['props']) ? json_encode($data['props']) : null;
        
        $stmt->execute([
            $data['type'],
            $data['label'],
            $data['description'] ?? null,
            $flags,
            $props,
            $data['canonical_key'] ?? null,
            $userId,
            $userId
        ]);
        
        $nodeId = $this->pdo->lastInsertId();
        
        // רישום ב-audit log
        $this->logAction('CREATE', 'node', $nodeId, null, $data, $userId);
        
        return $nodeId;
    }
    
    /**
     * עדכון צומת
     */
    public function update($id, $data, $userId = null) {
        // שמירת גרסה קודמת
        $this->saveVersion($id, $userId);
        
        // קבלת נתונים קודמים
        $oldData = $this->getById($id);
        
        $stmt = $this->pdo->prepare("
            UPDATE nodes 
            SET type = ?, label = ?, description = ?, flags = ?, props = ?, 
                canonical_key = ?, updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $flags = isset($data['flags']) ? json_encode($data['flags']) : null;
        $props = isset($data['props']) ? json_encode($data['props']) : null;
        
        $stmt->execute([
            $data['type'],
            $data['label'],
            $data['description'] ?? null,
            $flags,
            $props,
            $data['canonical_key'] ?? null,
            $userId,
            $id
        ]);
        
        // רישום ב-audit log
        $newData = $this->getById($id);
        $this->logAction('UPDATE', 'node', $id, $oldData, $newData, $userId);
        
        return true;
    }
    
    /**
     * קבלת צומת לפי ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM nodes WHERE id = ?");
        $stmt->execute([$id]);
        $node = $stmt->fetch();
        
        if ($node) {
            $node['flags'] = $node['flags'] ? json_decode($node['flags'], true) : [];
            $node['props'] = $node['props'] ? json_decode($node['props'], true) : [];
        }
        
        return $node;
    }
    
    /**
     * חיפוש צמתים
     */
    public function search($query, $type = null, $flags = null) {
        $sql = "SELECT * FROM nodes WHERE 1=1";
        $params = [];
        
        if ($query) {
            $sql .= " AND (label LIKE ? OR description LIKE ?)";
            $searchTerm = "%$query%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        if ($flags) {
            $sql .= " AND JSON_CONTAINS(flags, ?)";
            $params[] = json_encode($flags);
        }
        
        $sql .= " ORDER BY label";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $nodes = $stmt->fetchAll();
        
        foreach ($nodes as &$node) {
            $node['flags'] = $node['flags'] ? json_decode($node['flags'], true) : [];
            $node['props'] = $node['props'] ? json_decode($node['props'], true) : [];
        }
        
        return $nodes;
    }
    
    /**
     * קבלת צמתים לפי סוג
     */
    public function getByType($type) {
        $stmt = $this->pdo->prepare("SELECT * FROM nodes WHERE type = ? ORDER BY label");
        $stmt->execute([$type]);
        $nodes = $stmt->fetchAll();
        
        foreach ($nodes as &$node) {
            $node['flags'] = $node['flags'] ? json_decode($node['flags'], true) : [];
            $node['props'] = $node['props'] ? json_decode($node['props'], true) : [];
        }
        
        return $nodes;
    }
    
    /**
     * בדיקת כפילויות - מחפש צומת לפי canonical_key או type+label
     * מחזיר את ה-ID של הצומת הקיים או null אם לא נמצא
     */
    public function findDuplicate($data) {
        // אם יש canonical_key, חיפוש לפי זה
        if (!empty($data['canonical_key'])) {
            $stmt = $this->pdo->prepare("SELECT id FROM nodes WHERE canonical_key = ? AND type = ?");
            $stmt->execute([$data['canonical_key'], $data['type']]);
            $result = $stmt->fetch();
            if ($result) {
                return $result['id'];
            }
        }
        
        // אם אין canonical_key או לא נמצא, חיפוש לפי type+label
        if (!empty($data['label'])) {
            $stmt = $this->pdo->prepare("SELECT id FROM nodes WHERE type = ? AND label = ?");
            $stmt->execute([$data['type'], $data['label']]);
            $result = $stmt->fetch();
            if ($result) {
                return $result['id'];
            }
        }
        
        return null;
    }
    
    /**
     * מחיקת צומת
     */
    public function delete($id, $userId = null) {
        $oldData = $this->getById($id);
        
        $stmt = $this->pdo->prepare("DELETE FROM nodes WHERE id = ?");
        $stmt->execute([$id]);
        
        // רישום ב-audit log
        $this->logAction('DELETE', 'node', $id, $oldData, null, $userId);
        
        return true;
    }
    
    /**
     * שמירת גרסה קודמת
     */
    private function saveVersion($nodeId, $userId = null) {
        $node = $this->getById($nodeId);
        if (!$node) return;
        
        // קבלת מספר גרסה הבא
        $versionStmt = $this->pdo->prepare("SELECT MAX(version_number) as max_version FROM node_versions WHERE node_id = ?");
        $versionStmt->execute([$nodeId]);
        $versionData = $versionStmt->fetch();
        $nextVersion = ($versionData['max_version'] ?? 0) + 1;
        
        // שמירת גרסה
        $stmt = $this->pdo->prepare("
            INSERT INTO node_versions (node_id, version_number, type, label, description, flags, props, canonical_key, changed_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $nodeId,
            $nextVersion,
            $node['type'],
            $node['label'],
            $node['description'],
            json_encode($node['flags']),
            json_encode($node['props']),
            $node['canonical_key'],
            $userId
        ]);
    }
    
    /**
     * קבלת גרסאות קודמות
     */
    public function getVersions($nodeId) {
        $stmt = $this->pdo->prepare("
            SELECT nv.*, u.email as changed_by_email
            FROM node_versions nv
            LEFT JOIN users u ON nv.changed_by = u.id
            WHERE nv.node_id = ?
            ORDER BY nv.version_number DESC
        ");
        $stmt->execute([$nodeId]);
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

