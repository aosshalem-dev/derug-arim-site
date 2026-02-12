<?php
require_once(__DIR__ . '/../db/connection.php');

class Evidence {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDB();
    }
    
    /**
     * הוספת ראיה לקשר
     */
    public function addToEdge($edgeId, $data, $userId = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO evidence (edge_id, source_type, source_ref, quote_snippet, page, line_range, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $edgeId,
            $data['source_type'],
            $data['source_ref'],
            $data['quote_snippet'] ?? null,
            $data['page'] ?? null,
            $data['line_range'] ?? null,
            $userId
        ]);
        
        $evidenceId = $this->pdo->lastInsertId();
        
        // רישום ב-audit log
        $this->logAction('CREATE', 'evidence', $evidenceId, null, $data, $userId);
        
        return $evidenceId;
    }
    
    /**
     * קבלת כל הראיות של קשר
     */
    public function getByEdge($edgeId) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, u.email as created_by_email
            FROM evidence e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.edge_id = ?
            ORDER BY e.captured_at DESC
        ");
        $stmt->execute([$edgeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * מחיקת ראיה
     */
    public function delete($id, $userId = null) {
        $oldData = $this->getById($id);
        
        $stmt = $this->pdo->prepare("DELETE FROM evidence WHERE id = ?");
        $stmt->execute([$id]);
        
        // רישום ב-audit log
        $this->logAction('DELETE', 'evidence', $id, $oldData, null, $userId);
        
        return true;
    }
    
    /**
     * קבלת ראיה לפי ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM evidence WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
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

