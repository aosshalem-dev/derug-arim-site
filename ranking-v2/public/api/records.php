<?php
/**
 * Unified API endpoint for CRUD operations on records
 * Handles GET (list/single), POST (create/update), DELETE
 */

// Start output buffering FIRST
ob_start();

// Error handling setup
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Helper function to send error response
function sendErrorResponse($httpCode, $error, $details = null) {
    ob_clean();
    http_response_code($httpCode);
    $response = [
        'success' => false,
        'error' => $error
    ];
    if ($details !== null) {
        $response['details'] = $details;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

// Helper function to send success response
function sendSuccessResponse($data, $message = null) {
    ob_clean();
    $response = ['success' => true];
    if ($message) {
        $response['message'] = $message;
    }
    if (is_array($data)) {
        $response = array_merge($response, $data);
    } else {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ob_end_flush();
    exit;
}

// Helper function to log errors
function logError($message, $context = []) {
    $logMessage = "[records.php] " . $message;
    if (!empty($context)) {
        $logMessage .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    error_log($logMessage);
}

try {
    // Set headers FIRST before any output
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    // Load required files
    require_once __DIR__ . '/../../src/config/database.php';
    require_once __DIR__ . '/../../src/utils/ensure_metadata_columns.php';
    
    // Ensure metadata columns (suppress any output)
    ob_start();
    try {
        ensureMetadataColumns();
    } catch (Exception $e) {
        // Log but don't fail if columns already exist
        error_log("Metadata columns check failed: " . $e->getMessage());
    }
    ob_end_clean();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Handle different HTTP methods
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            sendErrorResponse(405, 'Method not allowed');
    }
    
} catch (Exception $e) {
    logError("Unhandled exception", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    sendErrorResponse(500, 'שגיאה לא צפויה', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

/**
 * Handle GET requests - list records or get single record
 */
function handleGet() {
    // Check if requesting single record
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        getSingleRecord((int)$_GET['id']);
        return;
    }
    
    // Otherwise, get list of records
    getRecordsList();
}

/**
 * Get single record by ID
 */
function getSingleRecord($id) {
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        sendErrorResponse(500, 'שגיאת חיבור למסד הנתונים: ' . $e->getMessage());
    }
    
    $query = "SELECT 
        id, url, created_at, source_type, year,
        organization_type, jurisdiction_level, geographic_scope,
        topic_category, document_type, target_audience,
        values_orientation, cultural_focus, zionism_references,
        identity_theme, historical_periods, language,
        accessibility_level, publication_format, period_referenced,
        temporal_scope, completeness, reliability_indicators,
        metadata_extracted_at, metadata_status, short_summary, failure_reason,
        relevance_level, manual_summary,
        ai_relevance_score, ai_relevance_reason, ai_relevance_status,
        organization_name
    FROM ranking_urls 
    WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בהכנת שאילתה: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בביצוע שאילתה: ' . $error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בקבלת תוצאות שאילתה');
    }
    
    if ($row = $result->fetch_assoc()) {
        $record = formatRecord($row);
        $stmt->close();
        closeDbConnection($conn);
        sendSuccessResponse(['record' => $record]);
    } else {
        $stmt->close();
        closeDbConnection($conn);
        sendErrorResponse(404, 'רשומה לא נמצאה');
    }
}

/**
 * Get list of records with filters
 */
function getRecordsList() {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 50;
    $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'id';
    $sortOrder = isset($_GET['sortOrder']) ? strtoupper($_GET['sortOrder']) : 'DESC';
    
    // Build filters
    $filters = [];
    $params = [];
    $types = "";
    
    if (!empty($_GET['searchUrl'])) {
        $filters[] = "url LIKE ?";
        $params[] = '%' . $_GET['searchUrl'] . '%';
        $types .= "s";
    }
    
    if (!empty($_GET['status'])) {
        $filters[] = "metadata_status = ?";
        $params[] = $_GET['status'];
        $types .= "s";
    }
    
    if (!empty($_GET['orgType'])) {
        $filters[] = "organization_type = ?";
        $params[] = $_GET['orgType'];
        $types .= "s";
    }
    
    if (!empty($_GET['topic'])) {
        $filters[] = "topic_category = ?";
        $params[] = $_GET['topic'];
        $types .= "s";
    }
    
    if (!empty($_GET['year'])) {
        $filters[] = "year = ?";
        $params[] = (int)$_GET['year'];
        $types .= "i";
    }
    
    // Support both aiRelevanceScore and aiRelevanceMin for backward compatibility
    $aiRelevanceValue = $_GET['aiRelevanceScore'] ?? $_GET['aiRelevanceMin'] ?? null;
    if (!empty($aiRelevanceValue)) {
        $filters[] = "ai_relevance_score >= ?";
        $params[] = (int)$aiRelevanceValue;
        $types .= "i";
    }
    
    // Support both onlyUnrated and only_unrated for backward compatibility
    $onlyUnrated = $_GET['onlyUnrated'] ?? $_GET['only_unrated'] ?? null;
    if ($onlyUnrated === '1' || $onlyUnrated === 'true' || $onlyUnrated === true) {
        $filters[] = "ai_relevance_score IS NULL";
    }
    
    // Validate sort
    $allowedSortColumns = ['id', 'url', 'created_at', 'year', 'metadata_status', 'organization_type', 'organization_name', 'topic_category', 'geographic_scope', 'relevance_level', 'ai_relevance_score'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'id';
    }
    
    if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
        $sortOrder = 'DESC';
    }
    
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        sendErrorResponse(500, 'שגיאת חיבור למסד הנתונים: ' . $e->getMessage());
    }
    
    $whereClause = !empty($filters) ? "WHERE " . implode(" AND ", $filters) : "";
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM ranking_urls $whereClause";
    $countStmt = $conn->prepare($countQuery);
    if (!$countStmt) {
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בהכנת שאילתת ספירה: ' . $conn->error);
    }
    
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    
    if (!$countStmt->execute()) {
        $error = $countStmt->error;
        $countStmt->close();
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בביצוע שאילתת ספירה: ' . $error);
    }
    
    $countResult = $countStmt->get_result();
    if (!$countResult) {
        $countStmt->close();
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בקבלת תוצאות ספירה');
    }
    
    $countRow = $countResult->fetch_assoc();
    $totalRecords = $countRow ? (int)$countRow['total'] : 0;
    $countStmt->close();
    
    // Calculate pagination
    $totalPages = ceil($totalRecords / $pageSize);
    $offset = ($page - 1) * $pageSize;
    
    // Get records
    $query = "SELECT 
        id, url, created_at, source_type, year,
        organization_type, jurisdiction_level, geographic_scope,
        topic_category, document_type, target_audience,
        values_orientation, cultural_focus, zionism_references,
        identity_theme, historical_periods, language,
        accessibility_level, publication_format, period_referenced,
        temporal_scope, completeness, reliability_indicators,
        metadata_extracted_at, metadata_status, short_summary, failure_reason,
        relevance_level, manual_summary,
        ai_relevance_score, ai_relevance_reason, ai_relevance_status,
        organization_name
    FROM ranking_urls 
    $whereClause
    ORDER BY $sortBy $sortOrder
    LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בהכנת שאילתה: ' . $conn->error);
    }
    
    $limitParam = $pageSize;
    $offsetParam = $offset;
    $allParams = array_merge($params, [$limitParam, $offsetParam]);
    $allTypes = $types . "ii";
    
    if (!empty($allParams)) {
        $stmt->bind_param($allTypes, ...$allParams);
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בביצוע שאילתה: ' . $error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בקבלת תוצאות שאילתה');
    }
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = formatRecord($row);
    }
    
    $stmt->close();
    
    // Get stats
    $statsQuery = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN metadata_status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM ranking_urls";
    $statsResult = $conn->query($statsQuery);
    if (!$statsResult) {
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בשאילתת סטטיסטיקה: ' . $conn->error);
    }
    
    $stats = $statsResult->fetch_assoc();
    if (!$stats) {
        $stats = ['total' => 0, 'pending' => 0];
    }
    
    closeDbConnection($conn);
    
    sendSuccessResponse([
        'records' => $records,
        'pagination' => [
            'page' => $page,
            'pageSize' => $pageSize,
            'totalRecords' => (int)$totalRecords,
            'totalPages' => $totalPages
        ],
        'stats' => [
            'total' => (int)$stats['total'],
            'pending' => (int)$stats['pending']
        ]
    ]);
}

/**
 * Format record from database row
 */
function formatRecord($row) {
    return [
        'id' => (int)$row['id'],
        'url' => $row['url'],
        'created_at' => $row['created_at'],
        'source_type' => $row['source_type'],
        'year' => $row['year'] ? (int)$row['year'] : null,
        'organization_type' => $row['organization_type'],
        'jurisdiction_level' => $row['jurisdiction_level'],
        'geographic_scope' => $row['geographic_scope'],
        'topic_category' => $row['topic_category'],
        'document_type' => $row['document_type'],
        'target_audience' => $row['target_audience'],
        'values_orientation' => $row['values_orientation'] ? json_decode($row['values_orientation'], true) : null,
        'cultural_focus' => $row['cultural_focus'],
        'zionism_references' => $row['zionism_references'],
        'identity_theme' => $row['identity_theme'] ? json_decode($row['identity_theme'], true) : null,
        'historical_periods' => $row['historical_periods'] ? json_decode($row['historical_periods'], true) : null,
        'language' => $row['language'],
        'accessibility_level' => $row['accessibility_level'],
        'publication_format' => $row['publication_format'],
        'period_referenced' => $row['period_referenced'],
        'temporal_scope' => $row['temporal_scope'],
        'completeness' => $row['completeness'],
        'reliability_indicators' => $row['reliability_indicators'] ? json_decode($row['reliability_indicators'], true) : null,
        'metadata_extracted_at' => $row['metadata_extracted_at'],
        'metadata_status' => $row['metadata_status'],
        'short_summary' => $row['short_summary'] ?? null,
        'failure_reason' => $row['failure_reason'] ?? null,
        'relevance_level' => isset($row['relevance_level']) && $row['relevance_level'] !== null ? (int)$row['relevance_level'] : null,
        'manual_summary' => $row['manual_summary'] ?? null,
        'ai_relevance_score' => isset($row['ai_relevance_score']) && $row['ai_relevance_score'] !== null ? (int)$row['ai_relevance_score'] : null,
        'ai_relevance_reason' => $row['ai_relevance_reason'] ?? null,
        'ai_relevance_status' => $row['ai_relevance_status'] ?? null,
        'organization_name' => $row['organization_name'] ?? null
    ];
}

/**
 * Handle POST requests - create or update record
 */
function handlePost() {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!$input) {
        sendErrorResponse(400, 'נתונים לא תקינים', ['json_error' => json_last_error_msg()]);
    }
    
    if (empty($input['url'])) {
        sendErrorResponse(400, 'URL הוא שדה חובה');
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : null;
    $isUpdate = $id !== null;
    
    $conn = getDbConnection();
    
    // Prepare JSON fields
    $valuesOrientation = !empty($input['values_orientation']) ? json_encode($input['values_orientation'], JSON_UNESCAPED_UNICODE) : null;
    $identityTheme = !empty($input['identity_theme']) ? json_encode($input['identity_theme'], JSON_UNESCAPED_UNICODE) : null;
    $historicalPeriods = !empty($input['historical_periods']) ? json_encode($input['historical_periods'], JSON_UNESCAPED_UNICODE) : null;
    $reliabilityIndicators = !empty($input['reliability_indicators']) ? json_encode($input['reliability_indicators'], JSON_UNESCAPED_UNICODE) : null;
    
    // Prepare other fields
    $shortSummary = isset($input['short_summary']) && !empty($input['short_summary']) ? trim($input['short_summary']) : null;
    $manualSummary = isset($input['manual_summary']) && !empty($input['manual_summary']) ? trim($input['manual_summary']) : null;
    
    // Handle relevance_level
    $relevanceLevel = null;
    if (isset($input['relevance_level']) && $input['relevance_level'] !== '' && $input['relevance_level'] !== null) {
        $relevanceLevelInt = (int)$input['relevance_level'];
        if ($relevanceLevelInt >= 1 && $relevanceLevelInt <= 5) {
            $relevanceLevel = $relevanceLevelInt;
        }
    }
    
    // Handle ai_relevance_score
    $aiRelevanceScore = null;
    if (isset($input['ai_relevance_score']) && $input['ai_relevance_score'] !== '' && $input['ai_relevance_score'] !== null) {
        $aiRelevanceScoreInt = (int)$input['ai_relevance_score'];
        if ($aiRelevanceScoreInt >= 1 && $aiRelevanceScoreInt <= 5) {
            $aiRelevanceScore = $aiRelevanceScoreInt;
        }
    }
    
    $aiRelevanceReason = isset($input['ai_relevance_reason']) && !empty($input['ai_relevance_reason']) ? trim($input['ai_relevance_reason']) : null;
    
    if ($isUpdate) {
        // Update existing record
        $query = "UPDATE ranking_urls SET
            url = ?,
            source_type = ?,
            year = ?,
            organization_name = ?,
            organization_type = ?,
            jurisdiction_level = ?,
            geographic_scope = ?,
            topic_category = ?,
            document_type = ?,
            target_audience = ?,
            values_orientation = ?,
            cultural_focus = ?,
            zionism_references = ?,
            identity_theme = ?,
            historical_periods = ?,
            language = ?,
            accessibility_level = ?,
            publication_format = ?,
            period_referenced = ?,
            temporal_scope = ?,
            completeness = ?,
            reliability_indicators = ?,
            short_summary = ?,
            relevance_level = ?,
            manual_summary = ?,
            ai_relevance_score = ?,
            ai_relevance_reason = ?,
            metadata_status = ?
        WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            closeDbConnection($conn);
            sendErrorResponse(500, 'שגיאה בהכנת שאילתת עדכון', $conn->error);
        }
        
        // Prepare variables for bind_param
        $url = $input['url'];
        $sourceType = $input['source_type'] ?? null;
        $year = isset($input['year']) && $input['year'] !== '' ? (int)$input['year'] : null;
        $organizationName = isset($input['organization_name']) && !empty($input['organization_name']) ? trim($input['organization_name']) : null;
        $organizationType = $input['organization_type'] ?? null;
        $jurisdictionLevel = $input['jurisdiction_level'] ?? null;
        $geographicScope = $input['geographic_scope'] ?? null;
        $topicCategory = $input['topic_category'] ?? null;
        $documentType = $input['document_type'] ?? null;
        $targetAudience = $input['target_audience'] ?? null;
        $culturalFocus = $input['cultural_focus'] ?? null;
        $zionismReferences = $input['zionism_references'] ?? null;
        $language = $input['language'] ?? null;
        $accessibilityLevel = $input['accessibility_level'] ?? null;
        $publicationFormat = $input['publication_format'] ?? null;
        $periodReferenced = $input['period_referenced'] ?? null;
        $temporalScope = $input['temporal_scope'] ?? null;
        $completeness = $input['completeness'] ?? null;
        $metadataStatus = $input['metadata_status'] ?? 'pending';
        
        $stmt->bind_param("ssissssssssssssssssssssisissi",
            $url, $sourceType, $year, $organizationName, $organizationType,
            $jurisdictionLevel, $geographicScope, $topicCategory, $documentType,
            $targetAudience, $valuesOrientation, $culturalFocus, $zionismReferences,
            $identityTheme, $historicalPeriods, $language, $accessibilityLevel,
            $publicationFormat, $periodReferenced, $temporalScope, $completeness,
            $reliabilityIndicators, $shortSummary, $relevanceLevel, $manualSummary,
            $aiRelevanceScore, $aiRelevanceReason, $metadataStatus, $id
        );

    } else {
        // Insert new record
        $query = "INSERT INTO ranking_urls (
            url, source_type, year, organization_name, organization_type, jurisdiction_level,
            geographic_scope, topic_category, document_type, target_audience,
            values_orientation, cultural_focus, zionism_references,
            identity_theme, historical_periods, language, accessibility_level,
            publication_format, period_referenced, temporal_scope,
            completeness, reliability_indicators, short_summary, relevance_level, manual_summary, ai_relevance_score, ai_relevance_reason, metadata_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            closeDbConnection($conn);
            sendErrorResponse(500, 'שגיאה בהכנת שאילתת הוספה', $conn->error);
        }
        
        // Prepare variables for bind_param
        $url = $input['url'];
        $sourceType = $input['source_type'] ?? null;
        $year = isset($input['year']) && $input['year'] !== '' ? (int)$input['year'] : null;
        $organizationName = isset($input['organization_name']) && !empty($input['organization_name']) ? trim($input['organization_name']) : null;
        $organizationType = $input['organization_type'] ?? null;
        $jurisdictionLevel = $input['jurisdiction_level'] ?? null;
        $geographicScope = $input['geographic_scope'] ?? null;
        $topicCategory = $input['topic_category'] ?? null;
        $documentType = $input['document_type'] ?? null;
        $targetAudience = $input['target_audience'] ?? null;
        $culturalFocus = $input['cultural_focus'] ?? null;
        $zionismReferences = $input['zionism_references'] ?? null;
        $language = $input['language'] ?? null;
        $accessibilityLevel = $input['accessibility_level'] ?? null;
        $publicationFormat = $input['publication_format'] ?? null;
        $periodReferenced = $input['period_referenced'] ?? null;
        $temporalScope = $input['temporal_scope'] ?? null;
        $completeness = $input['completeness'] ?? null;
        $metadataStatus = $input['metadata_status'] ?? 'pending';
        
        $stmt->bind_param("ssissssssssssssssssssssisiss",
            $url, $sourceType, $year, $organizationName, $organizationType,
            $jurisdictionLevel, $geographicScope, $topicCategory, $documentType,
            $targetAudience, $valuesOrientation, $culturalFocus, $zionismReferences,
            $identityTheme, $historicalPeriods, $language, $accessibilityLevel,
            $publicationFormat, $periodReferenced, $temporalScope, $completeness,
            $reliabilityIndicators, $shortSummary, $relevanceLevel, $manualSummary,
            $aiRelevanceScore, $aiRelevanceReason, $metadataStatus
        );
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בשמירת הרשומה', ['message' => $error]);
    }
    
    $recordId = $isUpdate ? $id : $conn->insert_id;
    $stmt->close();
    
    // Fetch the saved record
    $fetchQuery = "SELECT * FROM ranking_urls WHERE id = ?";
    $fetchStmt = $conn->prepare($fetchQuery);
    $fetchStmt->bind_param("i", $recordId);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();
    $record = $result->fetch_assoc();
    $fetchStmt->close();
    
    closeDbConnection($conn);
    
    sendSuccessResponse([
        'record' => formatRecord($record)
    ], $isUpdate ? 'רשומה עודכנה בהצלחה' : 'רשומה נוצרה בהצלחה');
}

/**
 * Handle DELETE requests
 */
function handleDelete() {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!isset($input['id']) || empty($input['id'])) {
        sendErrorResponse(400, 'מזהה רשומה נדרש');
    }
    
    $id = (int)$input['id'];
    $conn = getDbConnection();
    
    $query = "DELETE FROM ranking_urls WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        closeDbConnection($conn);
        sendSuccessResponse([], 'רשומה נמחקה בהצלחה');
    } else {
        $error = $stmt->error;
        $stmt->close();
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה במחיקת הרשומה', ['message' => $error]);
    }
}


