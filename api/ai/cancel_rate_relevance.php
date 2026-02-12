<?php
/**
 * API endpoint to cancel AI relevance rating job
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

function saveJobData($job_id, $data) {
    $job_file = sys_get_temp_dir() . '/ai_relevance_job_' . $job_id . '.json';
    file_put_contents($job_file, json_encode($data));
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = [];
    }
    
    $job_id = isset($input['job_id']) ? $input['job_id'] : (isset($_GET['job_id']) ? $_GET['job_id'] : null);
    if (!$job_id) {
        sendResponse(['success' => false, 'error' => 'job_id required']);
    }
    
    $job_data = getJobData($job_id);
    if (!$job_data) {
        sendResponse(['success' => false, 'error' => 'Job not found']);
    }
    
    $job_data['cancel'] = true;
    saveJobData($job_id, $job_data);
    
    sendResponse([
        'success' => true,
        'message' => 'Job cancelled',
        'progress' => $job_data
    ]);
    
} catch (Exception $e) {
    error_log("Error in cancel_rate_relevance: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'error' => 'שגיאה: ' . $e->getMessage()
    ]);
}

