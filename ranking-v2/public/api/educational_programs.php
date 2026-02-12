<?php
/**
 * API endpoint for educational_programs table
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');

function sendResponse($data) {
    ob_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    ob_end_flush();
    exit;
}

function sendErrorResponse($message, $code = 500) {
    http_response_code($code);
    sendResponse([
        'success' => false,
        'error' => $message
    ]);
}

try {
    // Load database config
    $db_file = __DIR__ . '/../../src/config/database.php';
    if (!file_exists($db_file)) {
        throw new Exception("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    $conn = getDbConnection();
    
    // Check if keywords column exists - use safer method
    $keywords_exists = false;
    try {
        $test_result = $conn->query("SELECT 1 FROM information_schema.COLUMNS 
                                      WHERE TABLE_SCHEMA = DATABASE() 
                                      AND TABLE_NAME = 'educational_programs' 
                                      AND COLUMN_NAME = 'keywords'");
        if ($test_result && $test_result->num_rows > 0) {
            $keywords_exists = true;
        }
    } catch (Exception $e) {
        // Column doesn't exist
        $keywords_exists = false;
    }
    
    // Get filters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $program_type = isset($_GET['program_type']) ? $_GET['program_type'] : '';
    $topic_category = isset($_GET['topic_category']) ? $_GET['topic_category'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10000;
    
    // Build query
    $where = [];
    $params = [];
    $types = '';
    
    if ($search) {
        $where[] = "(ep.program_name LIKE ? OR ep.description LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }
    
    if ($program_type) {
        $where[] = "ep.program_type = ?";
        $params[] = $program_type;
        $types .= 's';
    }
    
    if ($topic_category) {
        $where[] = "ep.topic_category = ?";
        $params[] = $topic_category;
        $types .= 's';
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM educational_programs ep $where_clause";
    $count_stmt = $conn->prepare($count_query);
    if ($params) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Get records with joins - order is important: must match table display order
    // Order in SELECT: program_id, program_name, description, organization_name, topic_category, 
    //                  target_audience, age_range, duration, program_type, keywords, url
    $offset = ($page - 1) * $pageSize;
    
    // Check which optional columns exist
    $check_columns = $conn->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS
                                    WHERE TABLE_SCHEMA = DATABASE()
                                    AND TABLE_NAME = 'educational_programs'
                                    AND COLUMN_NAME IN ('keywords', 'url')");
    $existing_columns = [];
    if ($check_columns) {
        while ($col_row = $check_columns->fetch_assoc()) {
            $existing_columns[] = $col_row['COLUMN_NAME'];
        }
    }
    $keywords_exists = in_array('keywords', $existing_columns);
    $direct_url_exists = in_array('url', $existing_columns);

    // Build SELECT - use direct url column if exists, otherwise JOIN
    $keywords_select = $keywords_exists ? 'ep.keywords' : 'NULL as keywords';
    $url_select = $direct_url_exists ? 'ep.url' : 'ru.url';

    $query = "SELECT ep.program_id,
                ep.program_name,
                ep.description,
                o.org_name as organization_name,
                ep.topic_category,
                ep.target_audience,
                ep.age_range,
                ep.duration,
                ep.program_type,
                $keywords_select,
                $url_select as url
              FROM educational_programs ep
              LEFT JOIN ranking_urls ru ON ep.url_id = ru.id
              LEFT JOIN organizations o ON ep.organization_id = o.org_id
              $where_clause
              ORDER BY ep.program_name ASC
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('שגיאה בהכנת שאילתה: ' . $conn->error);
    }
    
    if ($params) {
        $params[] = $pageSize;
        $params[] = $offset;
        $types .= 'ii';
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('ii', $pageSize, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    $stmt->close();
    closeDbConnection($conn);
    
    sendResponse([
        'success' => true,
        'programs' => $programs,
        'total' => $total,
        'page' => $page,
        'pageSize' => $pageSize
    ]);
    
} catch (Exception $e) {
    error_log("Error in educational_programs API: " . $e->getMessage());
    sendErrorResponse('שגיאה בטעינת תוכניות חינוכיות: ' . $e->getMessage());
}

