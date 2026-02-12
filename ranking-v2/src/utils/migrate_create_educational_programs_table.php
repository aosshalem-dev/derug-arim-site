<?php
/**
 * Migration script to create educational_programs table
 * This script is idempotent - safe to run multiple times
 */

require_once __DIR__ . '/../config/database.php';

function createEducationalProgramsTable($conn = null) {
    $should_close = false;
    if ($conn === null) {
        $conn = getDbConnection();
        $should_close = true;
    }
    
    if (!$conn) {
        throw new Exception('שגיאת חיבור למסד הנתונים');
    }
    
    // Check if table already exists
    $result = $conn->query("SHOW TABLES LIKE 'educational_programs'");
    if ($result && $result->num_rows > 0) {
        // Table exists, check structure
        $columns_result = $conn->query("SHOW COLUMNS FROM educational_programs");
        if ($columns_result) {
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Check if all required columns exist
            $required_columns = [
                'program_id', 'program_name', 'description', 'url_id', 'organization_id',
                'topic_category', 'target_audience', 'age_range', 'duration', 'location',
                'program_type', 'language', 'status', 'extracted_at', 'created_at'
            ];
            
            $missing_columns = array_diff($required_columns, $existing_columns);
            
            if (empty($missing_columns)) {
                if ($should_close) {
                    closeDbConnection($conn);
                }
                return [
                    'success' => true,
                    'message' => 'טבלת educational_programs כבר קיימת עם כל העמודות הנדרשות',
                    'action' => 'skipped'
                ];
            } else {
                // Some columns are missing, we'll recreate the table
                // First, drop foreign key constraints if exist
                $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                $conn->query("DROP TABLE IF EXISTS educational_programs");
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            }
        }
    }
    
    // Create the table
    $sql = "CREATE TABLE educational_programs (
        program_id INT AUTO_INCREMENT PRIMARY KEY,
        program_name VARCHAR(500) NOT NULL,
        description TEXT NULL,
        url_id INT NOT NULL,
        organization_id INT NULL,
        topic_category VARCHAR(100) NULL COMMENT 'קטגוריית נושא (education, culture, etc.)',
        target_audience VARCHAR(200) NULL COMMENT 'קהל יעד (students, educators, general_public, etc.)',
        age_range VARCHAR(50) NULL COMMENT 'טווח גילאים (e.g., 6-12, 13-18, adults)',
        duration VARCHAR(100) NULL COMMENT 'משך התוכנית (e.g., semester, year, ongoing)',
        location VARCHAR(200) NULL COMMENT 'מיקום התוכנית',
        program_type ENUM(
            'curriculum',
            'workshop',
            'course',
            'seminar',
            'event',
            'resource',
            'initiative',
            'other'
        ) DEFAULT 'other',
        language ENUM('hebrew', 'english', 'arabic', 'mixed', 'other') DEFAULT 'hebrew',
        status ENUM('active', 'archived', 'upcoming', 'unknown') DEFAULT 'unknown',
        extracted_at TIMESTAMP NULL COMMENT 'מתי חולץ המידע',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_url_id (url_id),
        INDEX idx_organization_id (organization_id),
        INDEX idx_topic_category (topic_category),
        INDEX idx_program_type (program_type),
        INDEX idx_status (status),
        CONSTRAINT fk_program_url
            FOREIGN KEY (url_id) REFERENCES ranking_urls(id)
            ON DELETE CASCADE,
        CONSTRAINT fk_program_organization
            FOREIGN KEY (organization_id) REFERENCES organizations(org_id)
            ON DELETE SET NULL
    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        if ($should_close) {
            closeDbConnection($conn);
        }
        throw new Exception("שגיאה ביצירת טבלת educational_programs: " . $conn->error);
    }
    
    if ($should_close) {
        closeDbConnection($conn);
    }
    
    return [
        'success' => true,
        'message' => 'טבלת educational_programs נוצרה בהצלחה',
        'action' => 'created'
    ];
}



