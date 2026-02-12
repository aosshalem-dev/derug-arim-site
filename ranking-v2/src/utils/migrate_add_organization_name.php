<?php
/**
 * Migration script to add organization_name column
 * This script is idempotent - safe to run multiple times
 */

require_once __DIR__ . '/../config/database.php';

function ensureOrganizationNameColumn() {
    $conn = getDbConnection();
    
    // Check which columns exist
    $result = $conn->query("SHOW COLUMNS FROM ranking_urls");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    if (!in_array('organization_name', $existing_columns)) {
        $sql = "ALTER TABLE ranking_urls 
                ADD COLUMN organization_name VARCHAR(255) NULL COMMENT 'שם הגוף (למשל שם העירייה)'";
        
        if (!$conn->query($sql)) {
            throw new Exception("Failed to add organization_name column: " . $conn->error);
        }
        
        // Add index for better performance
        $conn->query("ALTER TABLE ranking_urls ADD INDEX idx_organization_name (organization_name)");
    }
    
    closeDbConnection($conn);
    
    return ['success' => true, 'message' => 'organization_name column ensured'];
}





