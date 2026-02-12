<?php
/**
 * Migration script to add AI relevance rating columns
 * This script is idempotent - safe to run multiple times
 */

require_once __DIR__ . '/../config/database.php';

function ensureAiRelevanceColumns($conn = null) {
    $should_close = false;
    if ($conn === null) {
        $conn = getDbConnection();
        $should_close = true;
    }
    
    if (!$conn) {
        throw new Exception('שגיאת חיבור למסד הנתונים');
    }
    
    // Check which columns exist
    $result = $conn->query("SHOW COLUMNS FROM ranking_urls");
    if (!$result) {
        if ($should_close) {
            closeDbConnection($conn);
        }
        throw new Exception("שגיאה בבדיקת עמודות: " . $conn->error);
    }
    
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    $columns_to_add = [];
    
    if (!in_array('ai_relevance_score', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN ai_relevance_score TINYINT NULL COMMENT 'דירוג רלוונטיות AI (1-5)'";
    }
    
    if (!in_array('ai_relevance_reason', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN ai_relevance_reason VARCHAR(255) NULL COMMENT 'סיבה לדירוג או למה לא דורג'";
    }
    
    if (!in_array('ai_relevance_model', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN ai_relevance_model VARCHAR(64) NULL COMMENT 'מודל AI שביצע את הדירוג'";
    }
    
    if (!in_array('ai_relevance_updated_at', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN ai_relevance_updated_at DATETIME NULL COMMENT 'מתי בוצע הדירוג'";
    }
    
    if (!in_array('ai_relevance_status', $existing_columns)) {
        $columns_to_add[] = "ADD COLUMN ai_relevance_status ENUM('pending', 'done', 'skipped', 'error') DEFAULT NULL COMMENT 'סטטוס דירוג AI'";
    }
    
    if (!empty($columns_to_add)) {
        $sql = "ALTER TABLE ranking_urls " . implode(", ", $columns_to_add);
        if (!$conn->query($sql)) {
            if ($should_close) {
                closeDbConnection($conn);
            }
            throw new Exception("שגיאה בהוספת עמודות: " . $conn->error);
        }
    }
    
    // Check and add indexes
    $index_result = $conn->query("SHOW INDEXES FROM ranking_urls");
    if ($index_result) {
        $existing_indexes = [];
        while ($row = $index_result->fetch_assoc()) {
            $existing_indexes[] = $row['Key_name'];
        }
        
        if (!in_array('idx_ai_relevance_score', $existing_indexes)) {
            $conn->query("ALTER TABLE ranking_urls ADD INDEX idx_ai_relevance_score (ai_relevance_score)");
        }
        
        if (!in_array('idx_ai_relevance_status', $existing_indexes)) {
            $conn->query("ALTER TABLE ranking_urls ADD INDEX idx_ai_relevance_status (ai_relevance_status)");
        }
    }
    
    if ($should_close) {
        closeDbConnection($conn);
    }
    
    return ['success' => true, 'message' => 'AI relevance columns ensured'];
}


