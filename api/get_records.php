<?php
/**
 * API endpoint for fetching records with filtering, sorting, and pagination
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';
require_once '../ensure_metadata_columns.php';

// Ensure all columns exist (including short_summary and failure_reason)
ensureMetadataColumns();

// Get parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 50;
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'id';
$sortOrder = isset($_GET['sortOrder']) ? strtoupper($_GET['sortOrder']) : 'DESC';

// Filters
$filters = [];
$params = [];
$types = "";

// URL search
if (!empty($_GET['searchUrl'])) {
    $filters[] = "url LIKE ?";
    $params[] = '%' . $_GET['searchUrl'] . '%';
    $types .= "s";
}

// Status filter
if (!empty($_GET['status'])) {
    $filters[] = "metadata_status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Organization type filter
if (!empty($_GET['orgType'])) {
    $filters[] = "organization_type = ?";
    $params[] = $_GET['orgType'];
    $types .= "s";
}

// Topic category filter
if (!empty($_GET['topic'])) {
    $filters[] = "topic_category = ?";
    $params[] = $_GET['topic'];
    $types .= "s";
}

// Year filter
if (!empty($_GET['year'])) {
    $filters[] = "year = ?";
    $params[] = (int)$_GET['year'];
    $types .= "i";
}

// AI relevance score filter
if (!empty($_GET['aiRelevanceMin'])) {
    $filters[] = "ai_relevance_score >= ?";
    $params[] = (int)$_GET['aiRelevanceMin'];
    $types .= "i";
}

// Unrated filter
if (isset($_GET['unrated']) && $_GET['unrated'] === '1') {
    $filters[] = "ai_relevance_score IS NULL";
}

// Validate sort column
$allowedSortColumns = ['id', 'url', 'created_at', 'year', 'metadata_status', 'organization_type', 'topic_category'];
if (!in_array($sortBy, $allowedSortColumns)) {
    $sortBy = 'id';
}

// Validate sort order
if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
    $sortOrder = 'DESC';
}

$conn = getDbConnection();

// Build query
$whereClause = !empty($filters) ? "WHERE " . implode(" AND ", $filters) : "";

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM ranking_urls $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
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
    http_response_code(500);
    echo json_encode(['error' => 'שגיאה בהכנת שאילתה: ' . $conn->error]);
    exit;
}

// Bind parameters
$limitParam = $pageSize;
$offsetParam = $offset;
$allParams = array_merge($params, [$limitParam, $offsetParam]);
$allTypes = $types . "ii";

if (!empty($allParams)) {
    $stmt->bind_param($allTypes, ...$allParams);
}

$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = [
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

$stmt->close();

// Get stats
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN metadata_status = 'pending' THEN 1 ELSE 0 END) as pending
FROM ranking_urls";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

closeDbConnection($conn);

echo json_encode([
    'success' => true,
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
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

