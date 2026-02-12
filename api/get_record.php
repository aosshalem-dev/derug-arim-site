<?php
/**
 * API endpoint for fetching a single record by ID
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';
require_once '../ensure_metadata_columns.php';

// Ensure all columns exist (including short_summary and failure_reason)
ensureMetadataColumns();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'מזהה רשומה נדרש']);
    exit;
}

$id = (int)$_GET['id'];
$conn = getDbConnection();

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
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $record = [
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
    
    echo json_encode([
        'success' => true,
        'record' => $record
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'רשומה לא נמצאה']);
}

$stmt->close();
closeDbConnection($conn);

