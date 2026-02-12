<?php
/**
 * Migration script to create organizations table
 * This script is idempotent - safe to run multiple times
 */

require_once __DIR__ . '/../config/database.php';

function createOrganizationsTable($conn = null) {
    $should_close = false;
    if ($conn === null) {
        $conn = getDbConnection();
        $should_close = true;
    }
    
    if (!$conn) {
        throw new Exception('שגיאת חיבור למסד הנתונים');
    }
    
    // Check if table already exists
    $result = $conn->query("SHOW TABLES LIKE 'organizations'");
    if ($result && $result->num_rows > 0) {
        // Table exists, check structure
        $columns_result = $conn->query("SHOW COLUMNS FROM organizations");
        if ($columns_result) {
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Check if all required columns exist
            $required_columns = [
                'org_id', 'org_name', 'org_type', 'country', 'city',
                'parent_org_id', 'ideological_domain', 'notes', 'created_at'
            ];
            
            $missing_columns = array_diff($required_columns, $existing_columns);
            
            if (empty($missing_columns)) {
                if ($should_close) {
                    closeDbConnection($conn);
                }
                return [
                    'success' => true,
                    'message' => 'טבלת organizations כבר קיימת עם כל העמודות הנדרשות',
                    'action' => 'skipped'
                ];
            } else {
                // Some columns are missing, we'll recreate the table
                // First, drop foreign key constraint if exists
                $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                $conn->query("DROP TABLE IF EXISTS organizations");
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            }
        }
    }
    
    // Create the table
    $sql = "CREATE TABLE organizations (
        org_id INT AUTO_INCREMENT PRIMARY KEY,
        org_name VARCHAR(255) NOT NULL,
        org_type ENUM(
            'municipality',
            'ministry',
            'NGO',
            'academic',
            'foundation',
            'private',
            'international',
            'media',
            'other'
        ) NOT NULL,
        country VARCHAR(100) DEFAULT 'Israel',
        city VARCHAR(100) NULL,
        parent_org_id INT NULL,
        ideological_domain ENUM(
            'education',
            'culture',
            'climate',
            'gender',
            'democracy',
            'mixed',
            'unknown'
        ) DEFAULT 'unknown',
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_org_name (org_name),
        INDEX idx_org_type (org_type),
        INDEX idx_city (city),
        CONSTRAINT fk_parent_org
            FOREIGN KEY (parent_org_id) REFERENCES organizations(org_id)
            ON DELETE SET NULL
    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        if ($should_close) {
            closeDbConnection($conn);
        }
        throw new Exception("שגיאה ביצירת טבלת organizations: " . $conn->error);
    }
    
    if ($should_close) {
        closeDbConnection($conn);
    }
    
    return [
        'success' => true,
        'message' => 'טבלת organizations נוצרה בהצלחה',
        'action' => 'created'
    ];
}



