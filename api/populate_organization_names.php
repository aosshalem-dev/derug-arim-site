<?php
/**
 * API endpoint to populate organization_name column
 * Can be called from browser or via AJAX
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Get base directory - this file is in api/, so go up one level
$baseDir = dirname(__DIR__);

// Load required files with absolute paths
require_once $baseDir . '/config/database.php';
require_once $baseDir . '/database/migrate_add_organization_name.php';

// Load populate function directly (don't require the whole file to avoid output issues)
// Instead, we'll define the function here or include it properly
if (file_exists($baseDir . '/populate_organization_names.php')) {
    // Capture any output from the file
    ob_start();
    require_once $baseDir . '/populate_organization_names.php';
    ob_end_clean();
}

function sendResponse($data) {
    ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    ob_end_flush();
    exit;
}

try {
    // Ensure column exists
    ensureOrganizationNameColumn();
    
    // Get parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    $onlyEmpty = !isset($_GET['all']) || $_GET['all'] !== '1';
    
    // Call populate function (it returns array, not outputs)
    $result = populateOrganizationNames($limit, $onlyEmpty);
    
    sendResponse([
        'success' => true,
        'result' => $result,
        'message' => "עודכנו {$result['updated']} מתוך {$result['total']} רשומות"
    ]);
    
} catch (Exception $e) {
    error_log("Error in populate_organization_names API: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

