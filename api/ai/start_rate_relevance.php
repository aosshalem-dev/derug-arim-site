<?php
/**
 * API endpoint to start AI relevance rating job
 * Returns immediately with job_id
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../../config/database.php';
require_once '../../database/migrate_add_ai_relevance.php';

function sendResponse($data) {
    ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = [];
    }
    
    $only_unrated = isset($input['only_unrated']) ? (bool)$input['only_unrated'] : true;
    $limit = isset($input['limit']) ? (int)$input['limit'] : 0;
    
    // Ensure columns exist
    ensureAiRelevanceColumns();
    
    $conn = getDbConnection();
    
    // Build query
    $where = $only_unrated ? "WHERE ai_relevance_score IS NULL" : "";
    $limit_clause = $limit > 0 ? "LIMIT $limit" : "";
    
    $query = "SELECT COUNT(*) as total FROM ranking_urls $where";
    $result = $conn->query($query);
    $total = $result->fetch_assoc()['total'];
    
    // Generate job ID
    $job_id = 'job_' . time() . '_' . uniqid();
    
    // Store job info in a simple file (or could use DB table)
    $job_file = sys_get_temp_dir() . '/ai_relevance_job_' . $job_id . '.json';
    $job_data = [
        'job_id' => $job_id,
        'total' => (int)$total,
        'processed' => 0,
        'done' => 0,
        'skipped' => 0,
        'error' => 0,
        'current_url' => null,
        'started_at' => date('Y-m-d H:i:s'),
        'only_unrated' => $only_unrated,
        'limit' => $limit,
        'cancel' => false
    ];
    file_put_contents($job_file, json_encode($job_data));
    
    // Start background processing (for simplicity, we'll use a different approach)
    // In production, use a proper job queue. For now, we'll process synchronously
    // but return immediately and let client poll status
    
    closeDbConnection($conn);
    
    sendResponse([
        'success' => true,
        'job_id' => $job_id,
        'total' => (int)$total,
        'message' => 'Job started. Use GET /api/ai/rate-relevance/status?job_id=' . $job_id . ' to check progress.'
    ]);
    
} catch (Exception $e) {
    error_log("Error in start_rate_relevance: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'error' => 'שגיאה בהתחלת עבודה: ' . $e->getMessage()
    ]);
}

