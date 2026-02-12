<?php
/**
 * Test script to check if improve organization names function works
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

try {
    // Load database config
    $db_file = __DIR__ . '/../../src/config/database.php';
    if (!file_exists($db_file)) {
        throw new Exception("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    // Load utility function
    $util_file = __DIR__ . '/../../src/utils/improve_organization_names_hebrew.php';
    if (!file_exists($util_file)) {
        throw new Exception("Utility file not found: $util_file");
    }
    require_once $util_file;
    
    // Check API key
    if (!defined('OPENAI_API_KEY')) {
        sendResponse([
            'success' => false,
            'error' => 'OPENAI_API_KEY לא מוגדר'
        ]);
    }
    
    $api_key = OPENAI_API_KEY;
    if (empty($api_key) || $api_key === 'your-api-key-here') {
        sendResponse([
            'success' => false,
            'error' => 'OPENAI_API_KEY לא מוגדר כראוי'
        ]);
    }
    
    // Check if prompt files exist
    $prompt_file = __DIR__ . '/../../src/prompts/improve_organization_name_hebrew.txt';
    $system_file = __DIR__ . '/../../src/prompts/improve_organization_name_hebrew_system.txt';
    
    $checks = [
        'api_key_defined' => defined('OPENAI_API_KEY'),
        'api_key_not_empty' => !empty($api_key),
        'prompt_file_exists' => file_exists($prompt_file),
        'system_file_exists' => file_exists($system_file),
        'prompt_loader_exists' => function_exists('loadPrompt'),
        'prompt_loader_system_exists' => function_exists('loadSystemMessage'),
    ];
    
    // Try to test with a simple URL
    try {
        $test_result = improveOrganizationNameWithAI(
            'https://he.wikipedia.org/wiki/Test',
            'Wikipedia',
            'media'
        );
        $checks['test_call_success'] = true;
        $checks['test_result'] = $test_result;
    } catch (Exception $e) {
        $checks['test_call_success'] = false;
        $checks['test_error'] = $e->getMessage();
    }
    
    sendResponse([
        'success' => true,
        'checks' => $checks,
        'message' => 'בדיקה הושלמה'
    ]);
    
} catch (Exception $e) {
    error_log("Error in test: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ", Line: " . $e->getLine());
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}



