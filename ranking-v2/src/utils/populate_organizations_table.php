<?php
/**
 * Populate organizations table from ranking_urls
 * Extracts unique organization names and types from ranking_urls table
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/migrate_create_organizations_table.php';

function populateOrganizationsTable($conn = null) {
    $should_close = false;
    if ($conn === null) {
        $conn = getDbConnection();
        $should_close = true;
    }
    
    if (!$conn) {
        throw new Exception('שגיאת חיבור למסד הנתונים');
    }
    
    // Ensure organizations table exists
    try {
        $create_result = createOrganizationsTable($conn);
        // Continue even if table already exists
    } catch (Exception $e) {
        // Table might already exist or other error, continue anyway
        error_log("Note: createOrganizationsTable returned: " . $e->getMessage());
    }
    
    // Check if organization_name column exists in ranking_urls
    $columns_result = $conn->query("SHOW COLUMNS FROM ranking_urls LIKE 'organization_name'");
    if (!$columns_result || $columns_result->num_rows === 0) {
        if ($should_close) {
            closeDbConnection($conn);
        }
        throw new Exception('עמודת organization_name לא קיימת בטבלת ranking_urls');
    }
    
    // Map organization_type from ranking_urls to organizations.org_type
    $type_mapping = [
        'municipality' => 'municipality',
        'government_agency' => 'ministry',
        'media' => 'media',
        'educational_institution' => 'academic',
        'ngo' => 'NGO',
        'research_institution' => 'academic',
        'other' => 'other'
    ];
    
    // Get unique organizations from ranking_urls
    $query = "SELECT 
                organization_name,
                GROUP_CONCAT(DISTINCT organization_type) as organization_types,
                COUNT(*) as url_count
              FROM ranking_urls 
              WHERE organization_name IS NOT NULL 
                AND organization_name != ''
                AND organization_name != 'NULL'
              GROUP BY organization_name
              ORDER BY organization_name";
    
    $result = $conn->query($query);
    
    if (!$result) {
        if ($should_close) {
            closeDbConnection($conn);
        }
        throw new Exception('שגיאה בשאילתת ranking_urls: ' . $conn->error);
    }
    
    $stats = [
        'total_found' => 0,
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0
    ];
    
    $insert_stmt = $conn->prepare("INSERT INTO organizations 
        (org_name, org_type, country, notes) 
        VALUES (?, ?, 'Israel', ?)
        ON DUPLICATE KEY UPDATE 
            org_type = VALUES(org_type),
            notes = CONCAT(COALESCE(notes, ''), IF(COALESCE(notes, '') != '', ' | ', ''), VALUES(notes))");
    
    if (!$insert_stmt) {
        if ($should_close) {
            closeDbConnection($conn);
        }
        throw new Exception('שגיאה בהכנת שאילתת INSERT: ' . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $stats['total_found']++;
        
        $org_name = trim($row['organization_name']);
        $org_types_raw = $row['organization_types'] ? explode(',', $row['organization_types']) : [];
        $url_count = (int)$row['url_count'];
        
        // Map organization_type - use first type or 'other'
        $org_type_raw = !empty($org_types_raw) ? trim($org_types_raw[0]) : null;
        $org_type = isset($type_mapping[$org_type_raw]) 
            ? $type_mapping[$org_type_raw] 
            : 'other';
        
        // Create notes with URL count and types
        $types_str = !empty($org_types_raw) ? implode(', ', array_unique(array_filter($org_types_raw))) : 'unknown';
        $notes = "Imported from ranking_urls. Associated with {$url_count} URL(s). Types: {$types_str}";
        
        // Check if organization already exists
        $check_query = "SELECT org_id FROM organizations WHERE org_name = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $org_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->num_rows > 0;
        $check_stmt->close();
        
        // Insert or update
        $insert_stmt->bind_param("sss", $org_name, $org_type, $notes);
        
        if ($insert_stmt->execute()) {
            if ($exists) {
                $stats['updated']++;
            } else {
                $stats['inserted']++;
            }
        } else {
            $stats['errors']++;
            error_log("Error inserting organization '{$org_name}': " . $insert_stmt->error);
        }
    }
    
    $insert_stmt->close();
    
    if ($should_close) {
        closeDbConnection($conn);
    }
    
    return $stats;
}

