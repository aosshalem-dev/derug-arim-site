<?php
/**
 * API endpoint for organizations table
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
    
    // Get filters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $org_type = isset($_GET['org_type']) ? $_GET['org_type'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10000;
    
    // Build query
    $where = [];
    $params = [];
    $types = '';
    
    if ($search) {
        $where[] = "(org_name LIKE ? OR notes LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }
    
    if ($org_type) {
        $where[] = "org_type = ?";
        $params[] = $org_type;
        $types .= 's';
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM organizations $where_clause";
    $count_stmt = $conn->prepare($count_query);
    if ($params) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Get records
    $offset = ($page - 1) * $pageSize;
    $query = "SELECT o.*, 
                (SELECT COUNT(*) FROM ranking_urls WHERE organization_name = o.org_name) as url_count
              FROM organizations o
              $where_clause
              ORDER BY o.org_name ASC
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
    
    $organizations = [];
    while ($row = $result->fetch_assoc()) {
        $organizations[] = $row;
    }
    
    $stmt->close();
    closeDbConnection($conn);
    
    sendResponse([
        'success' => true,
        'organizations' => $organizations,
        'total' => $total,
        'page' => $page,
        'pageSize' => $pageSize
    ]);
    
} catch (Exception $e) {
    error_log("Error in organizations API: " . $e->getMessage());
    sendErrorResponse('שגיאה בטעינת ארגונים: ' . $e->getMessage());
}



