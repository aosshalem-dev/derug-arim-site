<?php
/**
 * API endpoint to check AI relevance rating job status
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function sendResponse($data) {
    ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

function getJobData($job_id) {
    $job_file = sys_get_temp_dir() . '/ai_relevance_job_' . $job_id . '.json';
    if (!file_exists($job_file)) {
        return null;
    }
    return json_decode(file_get_contents($job_file), true);
}

try {
    $job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;
    if (!$job_id) {
        sendResponse(['success' => false, 'error' => 'job_id required']);
    }
    
    $job_data = getJobData($job_id);
    if (!$job_data) {
        sendResponse(['success' => false, 'error' => 'Job not found']);
    }
    
    sendResponse([
        'success' => true,
        'progress' => $job_data,
        'completed' => $job_data['processed'] >= $job_data['total'],
        'cancelled' => $job_data['cancel'] ?? false
    ]);
    
} catch (Exception $e) {
    error_log("Error in rate-relevance-status: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'error' => 'שגיאה: ' . $e->getMessage()
    ]);
}

