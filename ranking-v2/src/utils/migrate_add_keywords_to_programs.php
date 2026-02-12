<?php
/**
 * Migration script to add keywords column to educational_programs table
 * This script is idempotent - safe to run multiple times
 */

require_once __DIR__ . '/../config/database.php';

function addKeywordsColumnToPrograms($conn = null) {
    $should_close = false;
    if ($conn === null) {
        $conn = getDbConnection();
        $should_close = true;
    }
    
    if (!$conn) {
        throw new Exception('שגיאת חיבור למסד הנתונים');
    }
    
    // Check if column already exists
    $result = $conn->query("SHOW COLUMNS FROM educational_programs LIKE 'keywords'");
    if ($result && $result->num_rows > 0) {
        if ($should_close) {
            closeDbConnection($conn);
        }
        return [
            'success' => true,
            'message' => 'עמודת keywords כבר קיימת',
            'action' => 'skipped'
        ];
    }
    
    // Add keywords column
    $sql = "ALTER TABLE educational_programs 
            ADD COLUMN keywords TEXT NULL COMMENT 'מילות מפתח רלוונטיות (מופרדות בפסיקים) - למשל: SEL, מגוון, חוסן נפשי, זהות, ציונות'";
    
    if (!$conn->query($sql)) {
        if ($should_close) {
            closeDbConnection($conn);
        }
        throw new Exception("שגיאה בהוספת עמודת keywords: " . $conn->error);
    }
    
    // Add index for better search performance
    $conn->query("ALTER TABLE educational_programs ADD FULLTEXT INDEX idx_keywords (keywords)");
    
    if ($should_close) {
        closeDbConnection($conn);
    }
    
    return [
        'success' => true,
        'message' => 'עמודת keywords נוספה בהצלחה',
        'action' => 'added'
    ];
}



