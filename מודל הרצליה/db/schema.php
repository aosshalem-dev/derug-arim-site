<?php
/**
 * Database Schema Creator
 * יוצר את כל הטבלאות הנדרשות
 * להריץ פעם אחת או כשצריך ליצור מחדש
 */

require_once(__DIR__ . '/connection.php');

$pdo = getDB();

echo "<h2>יצירת טבלאות DB</h2>";

try {
    // טבלת משתמשים/עורכים
    echo "<p>יוצר טבלת users...</p>";
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'editor') DEFAULT 'editor',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME NULL,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✓ טבלת users נוצרה</p>";
    
    // יצירת משתמש admin ראשוני (אם לא קיים)
    $adminEmail = 'admin@gnostocracy.com';
    $adminPassword = 'gnostocracy7654';
    $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$adminEmail, $adminHash]);
        echo "<p style='color: green;'>✓ משתמש admin נוצר (email: $adminEmail, password: $adminPassword)</p>";
    } else {
        echo "<p style='color: orange;'>⚠ משתמש admin כבר קיים</p>";
    }
    
    // טבלת nodes (צמתים)
    echo "<p>יוצר טבלת nodes...</p>";
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS nodes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('org', 'person', 'program', 'term', 'concept', 'doc', 'funding', 'event', 'article') NOT NULL,
        label VARCHAR(255) NOT NULL,
        description TEXT,
        flags JSON NULL COMMENT 'תיוגים: problematic, suspicious, key_player, academic, ideology',
        props JSON NULL COMMENT 'מאפיינים נוספים',
        canonical_key VARCHAR(255) NULL,
        created_by INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_by INT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_type (type),
        INDEX idx_label (label),
        INDEX idx_canonical (type, canonical_key),
        INDEX idx_created_by (created_by),
        INDEX idx_updated_by (updated_by),
        UNIQUE KEY uniq_node (type, canonical_key),
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✓ טבלת nodes נוצרה</p>";
    
    // טבלת edges (קשרים)
    echo "<p>יוצר טבלת edges...</p>";
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS edges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_node_id INT NOT NULL,
        to_node_id INT NOT NULL,
        rel_type VARCHAR(64) NOT NULL COMMENT 'FUNDED_BY, PARTNERED_WITH, EMPLOYED_AT, PROMOTES, QUOTED, USES_TERM, ADVOCATES, CONTAINS_TERM, DEFINES, INFLUENCED_BY, REPORTS_ON, RESPONDED_TO, INVOLVED_IN, PROMOTED, OCCURRED_AT',
        confidence ENUM('high', 'medium', 'low') DEFAULT 'medium',
        start_date DATE NULL,
        end_date DATE NULL,
        props JSON NULL COMMENT 'סכום, הערות, וכו',
        created_by INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_by INT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_from (from_node_id),
        INDEX idx_to (to_node_id),
        INDEX idx_rel_type (rel_type),
        INDEX idx_created_by (created_by),
        FOREIGN KEY (from_node_id) REFERENCES nodes(id) ON DELETE CASCADE,
        FOREIGN KEY (to_node_id) REFERENCES nodes(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✓ טבלת edges נוצרה</p>";
    
    // טבלת evidence (ראיות)
    echo "<p>יוצר טבלת evidence...</p>";
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS evidence (
        id INT AUTO_INCREMENT PRIMARY KEY,
        edge_id INT NOT NULL,
        source_type ENUM('url', 'pdf', 'quote', 'image', 'screenshot') NOT NULL,
        source_ref TEXT NOT NULL COMMENT 'URL או נתיב לקובץ',
        quote_snippet TEXT NULL COMMENT 'ציטוט רלוונטי',
        page VARCHAR(32) NULL,
        line_range VARCHAR(64) NULL,
        created_by INT NULL,
        captured_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_edge (edge_id),
        INDEX idx_created_by (created_by),
        FOREIGN KEY (edge_id) REFERENCES edges(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✓ טבלת evidence נוצרה</p>";
    
    // טבלת audit_log (יומן פעולות)
    echo "<p>יוצר טבלת audit_log...</p>";
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action_type VARCHAR(50) NOT NULL COMMENT 'CREATE, UPDATE, DELETE',
        entity_type VARCHAR(50) NOT NULL COMMENT 'node, edge, evidence',
        entity_id INT NOT NULL,
        old_data JSON NULL COMMENT 'נתונים לפני השינוי',
        new_data JSON NULL COMMENT 'נתונים אחרי השינוי',
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_entity (entity_type, entity_id),
        INDEX idx_action (action_type),
        INDEX idx_created (created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✓ טבלת audit_log נוצרה</p>";
    
    // טבלת node_versions (גרסאות קודמות של nodes)
    echo "<p>יוצר טבלת node_versions...</p>";
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS node_versions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        node_id INT NOT NULL,
        version_number INT NOT NULL,
        type VARCHAR(50) NOT NULL,
        label VARCHAR(255) NOT NULL,
        description TEXT,
        flags JSON NULL,
        props JSON NULL,
        canonical_key VARCHAR(255) NULL,
        changed_by INT NULL,
        changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_node (node_id),
        INDEX idx_version (node_id, version_number),
        FOREIGN KEY (node_id) REFERENCES nodes(id) ON DELETE CASCADE,
        FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✓ טבלת node_versions נוצרה</p>";
    
    // טבלת edge_versions (גרסאות קודמות של edges)
    echo "<p>יוצר טבלת edge_versions...</p>";
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS edge_versions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        edge_id INT NOT NULL,
        version_number INT NOT NULL,
        from_node_id INT NOT NULL,
        to_node_id INT NOT NULL,
        rel_type VARCHAR(64) NOT NULL,
        confidence VARCHAR(20),
        start_date DATE NULL,
        end_date DATE NULL,
        props JSON NULL,
        changed_by INT NULL,
        changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_edge (edge_id),
        INDEX idx_version (edge_id, version_number),
        FOREIGN KEY (edge_id) REFERENCES edges(id) ON DELETE CASCADE,
        FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p style='color: green;'>✓ טבלת edge_versions נוצרה</p>";
    
    echo "<hr>";
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓✓✓ כל הטבלאות נוצרו בהצלחה! ✓✓✓</p>";
    echo "<p><a href='../auth/login.php'>המשך לכניסה</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ שגיאה: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

