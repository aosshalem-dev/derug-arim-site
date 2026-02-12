<?php
/**
 * API endpoint to populate organization_name column
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/utils/migrate_add_organization_name.php';
require_once __DIR__ . '/../../src/utils/populate_organization_names.php';

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
    
    // Call populate function
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


