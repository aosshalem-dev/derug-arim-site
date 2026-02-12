<?php
/**
 * תיקון: הוספת עמודת flags לטבלת nodes
 */
require_once(__DIR__ . '/connection.php');

$pdo = getDB();

echo "<h2>תיקון טבלת nodes - הוספת עמודת flags</h2>";

try {
    // בדיקה אם העמודה כבר קיימת
    $stmt = $pdo->query("SHOW COLUMNS FROM nodes LIKE 'flags'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "<p style='color: orange;'>⚠ עמודת flags כבר קיימת</p>";
    } else {
        // הוספת העמודה
        echo "<p>מוסיף עמודת flags...</p>";
        $pdo->exec("
            ALTER TABLE nodes 
            ADD COLUMN flags JSON NULL 
            COMMENT 'תיוגים: problematic, suspicious, key_player, academic, ideology'
            AFTER description
        ");
        echo "<p style='color: green;'>✓ עמודת flags נוספה בהצלחה!</p>";
    }
    
    // בדיקה אם יש עוד עמודות חסרות
    echo "<h3>בודק עמודות נוספות...</h3>";
    
    $requiredColumns = [
        'props' => "JSON NULL COMMENT 'מאפיינים נוספים'",
        'canonical_key' => "VARCHAR(255) NULL COMMENT 'מפתח ייחודי למניעת כפילויות'",
        'created_by' => "INT NULL",
        'updated_by' => "INT NULL"
    ];
    
    foreach ($requiredColumns as $colName => $colDef) {
        $stmt = $pdo->query("SHOW COLUMNS FROM nodes LIKE '$colName'");
        $exists = $stmt->fetch();
        
        if (!$exists) {
            echo "<p>מוסיף עמודת $colName...</p>";
            if ($colName === 'props') {
                $pdo->exec("ALTER TABLE nodes ADD COLUMN $colName $colDef AFTER flags");
            } elseif ($colName === 'canonical_key') {
                $pdo->exec("ALTER TABLE nodes ADD COLUMN $colName $colDef AFTER props");
            } elseif ($colName === 'created_by') {
                $pdo->exec("ALTER TABLE nodes ADD COLUMN $colName $colDef, ADD INDEX idx_created_by (created_by)");
            } elseif ($colName === 'updated_by') {
                $pdo->exec("ALTER TABLE nodes ADD COLUMN $colName $colDef, ADD INDEX idx_updated_by (updated_by)");
            }
            echo "<p style='color: green;'>✓ עמודת $colName נוספה</p>";
        } else {
            echo "<p style='color: green;'>✓ עמודת $colName קיימת</p>";
        }
    }
    
    // הוספת Foreign Keys אם חסרים
    echo "<h3>בודק Foreign Keys...</h3>";
    
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'nodes' 
        AND COLUMN_NAME = 'created_by' 
        AND CONSTRAINT_NAME LIKE 'fk_%'
    ");
    $fkExists = $stmt->fetch();
    
    if (!$fkExists) {
        echo "<p>מוסיף Foreign Keys...</p>";
        $pdo->exec("
            ALTER TABLE nodes 
            ADD CONSTRAINT fk_nodes_created_by 
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ");
        $pdo->exec("
            ALTER TABLE nodes 
            ADD CONSTRAINT fk_nodes_updated_by 
            FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
        ");
        echo "<p style='color: green;'>✓ Foreign Keys נוספו</p>";
    } else {
        echo "<p style='color: green;'>✓ Foreign Keys קיימים</p>";
    }
    
    // בדיקת UNIQUE constraint
    $stmt = $pdo->query("SHOW INDEXES FROM nodes WHERE Key_name = 'uniq_node'");
    $uniqueExists = $stmt->fetch();
    
    if (!$uniqueExists) {
        echo "<p>מוסיף UNIQUE constraint...</p>";
        $pdo->exec("
            ALTER TABLE nodes 
            ADD UNIQUE KEY uniq_node (type, canonical_key)
        ");
        echo "<p style='color: green;'>✓ UNIQUE constraint נוסף</p>";
    } else {
        echo "<p style='color: green;'>✓ UNIQUE constraint קיים</p>";
    }
    
    echo "<hr>";
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓✓✓ כל התיקונים הושלמו! ✓✓✓</p>";
    echo "<p><a href='../pages/add_node.php'>חזור להוספת צומת</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ שגיאה: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

