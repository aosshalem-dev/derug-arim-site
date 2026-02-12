<?php
/**
 * Migration script to convert ENUM columns to VARCHAR for free text support
 * Converts organization_type and topic_category from ENUM to VARCHAR(255)
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Migration: Convert ENUM to VARCHAR ===\n\n";

try {
    $conn = getDbConnection();

    // Check current column types
    echo "Checking current column types...\n";

    $result = $conn->query("SHOW COLUMNS FROM ranking_urls WHERE Field IN ('organization_type', 'topic_category')");
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']}: {$row['Type']}\n";
    }

    echo "\n";

    // Convert organization_type from ENUM to VARCHAR
    echo "Converting organization_type to VARCHAR(255)...\n";
    $sql1 = "ALTER TABLE ranking_urls MODIFY COLUMN organization_type VARCHAR(255) NULL COMMENT 'סוג הארגון המפרסם - טקסט חופשי'";

    if ($conn->query($sql1)) {
        echo "  SUCCESS: organization_type converted to VARCHAR(255)\n";
    } else {
        echo "  ERROR: " . $conn->error . "\n";
    }

    // Convert topic_category from ENUM to VARCHAR
    echo "Converting topic_category to VARCHAR(255)...\n";
    $sql2 = "ALTER TABLE ranking_urls MODIFY COLUMN topic_category VARCHAR(255) NULL COMMENT 'קטגוריית נושא - טקסט חופשי'";

    if ($conn->query($sql2)) {
        echo "  SUCCESS: topic_category converted to VARCHAR(255)\n";
    } else {
        echo "  ERROR: " . $conn->error . "\n";
    }

    echo "\n";

    // Verify changes
    echo "Verifying changes...\n";
    $result = $conn->query("SHOW COLUMNS FROM ranking_urls WHERE Field IN ('organization_type', 'topic_category')");
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']}: {$row['Type']}\n";
    }

    closeDbConnection($conn);

    echo "\n=== Migration completed ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
