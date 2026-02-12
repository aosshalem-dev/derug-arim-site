<?php
/**
 * API endpoint for creating summaries using GPT-4o
 * Enhanced with detailed error logging and better error messages
 */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

// Initial checks - before headers
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'גרסת PHP לא נתמכת',
        'details' => 'נדרשת PHP 7.0 או גבוה יותר. גרסה נוכחית: ' . PHP_VERSION
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check required extensions
$required_extensions = ['curl', 'json'];
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}
if (!empty($missing_extensions)) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Extensions חסרות',
        'details' => 'נדרשות ה-extensions הבאות: ' . implode(', ', $missing_extensions)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// PHP compatibility helper functions
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

// Log function for debugging
function logError($message, $context = []) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($context)) {
        $logMessage .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    error_log($logMessage);
}

// Function to send error response
function sendError($httpCode, $message, $details = null) {
    http_response_code($httpCode);
    $response = ['success' => false, 'error' => $message];
    if ($details !== null) {
        $response['details'] = $details;
    }
    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    logError("=== CREATE SUMMARY REQUEST START ===");
    
    // Load required files
    require_once __DIR__ . '/../../src/config/database.php';
    require_once __DIR__ . '/../../src/config/api_key.php';
    require_once __DIR__ . '/../../src/lib/url_fetcher.php';
    require_once __DIR__ . '/../../src/lib/prompt_loader.php';
    
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError(400, 'שגיאה בפורמט JSON', json_last_error_msg());
    }
    
    if (!isset($input['id']) || empty($input['id'])) {
        sendError(400, 'מזהה רשומה נדרש');
    }
    
    $id = (int)$input['id'];
    
    // Database connection
    $conn = getDbConnection();
    
    // Get record data
    $query = "SELECT 
        id, url, source_type, year, organization_type, jurisdiction_level,
        geographic_scope, topic_category, document_type, target_audience,
        values_orientation, cultural_focus, zionism_references, identity_theme,
        historical_periods, language, accessibility_level, publication_format,
        period_referenced, temporal_scope, completeness, reliability_indicators
    FROM ranking_urls 
    WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$row = $result->fetch_assoc()) {
        sendError(404, 'רשומה לא נמצאה');
    }
    
    $url = $row['url'];
    
    // Fetch URL content
    try {
        $urlContent = fetchWebpageContent($url);
    } catch (Exception $e) {
        $urlContent = "[לא ניתן למשוך תוכן מהקישור: " . $e->getMessage() . "]";
    }
    
    // Build metadata summary string
    $metadataParts = [];
    if ($row['source_type']) $metadataParts[] = "סוג מקור: " . $row['source_type'];
    if ($row['year']) $metadataParts[] = "שנה: " . $row['year'];
    if ($row['organization_type']) $metadataParts[] = "סוג ארגון: " . $row['organization_type'];
    if ($row['jurisdiction_level']) $metadataParts[] = "רמת סמכות: " . $row['jurisdiction_level'];
    if ($row['geographic_scope']) $metadataParts[] = "היקף גיאוגרפי: " . $row['geographic_scope'];
    if ($row['topic_category']) $metadataParts[] = "קטגוריית נושא: " . $row['topic_category'];
    if ($row['document_type']) $metadataParts[] = "סוג מסמך: " . $row['document_type'];
    if ($row['target_audience']) $metadataParts[] = "קהל יעד: " . $row['target_audience'];
    if ($row['cultural_focus']) $metadataParts[] = "מוקד תרבותי: " . $row['cultural_focus'];
    if ($row['zionism_references']) $metadataParts[] = "התייחסויות לציונות: " . $row['zionism_references'];
    if ($row['language']) $metadataParts[] = "שפה: " . $row['language'];
    
    $metadataString = implode(", ", $metadataParts);
    
    // Build prompt for GPT-4o from template
    $contentSection = '';
    if (!empty($urlContent) && !str_starts_with($urlContent, '[')) {
        if (function_exists('mb_substr')) {
            $contentSection = "תוכן הדף:\n" . mb_substr($urlContent, 0, 5000) . "\n\n";
        } else {
            $contentSection = "תוכן הדף:\n" . substr($urlContent, 0, 5000) . "\n\n";
        }
    }
    
    $metadataSection = '';
    if (!empty($metadataString)) {
        $metadataSection = "מטא-דאטה קיימת:\n$metadataString\n\n";
    }
    
    $prompt = loadPrompt('summary_creation', [
        'URL' => $url,
        'CONTENT_SECTION' => $contentSection,
        'METADATA_SECTION' => $metadataSection
    ]);
    
    // Check API key
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        sendError(500, 'מפתח API לא מוגדר');
    }
    
    $api_key = OPENAI_API_KEY;
    $api_url = 'https://api.openai.com/v1/chat/completions';
    
    $systemMessage = loadSystemMessage('summary_creation');
    if (empty($systemMessage)) {
        $systemMessage = 'אתה עוזר מקצועי ליצירת סיכומים בעברית. תמיד החזר סיכום קצר ומדויק בעברית בלבד, ללא הסברים נוספים.';
    }
    
    // Prepare API request
    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => $systemMessage
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.5,
        'max_tokens' => 500
    ];
    
    // Call GPT-4o API
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ],
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    
    curl_close($ch);
    
    if ($curl_errno !== 0) {
        sendError(500, 'שגיאה בחיבור ל-OpenAI API', "cURL error ($curl_errno): $curl_error");
    }
    
    if ($http_code !== 200) {
        $error_data = json_decode($response, true);
        $error_msg = isset($error_data['error']['message']) ? $error_data['error']['message'] : "HTTP error: $http_code";
        sendError(500, 'שגיאה ב-OpenAI API', ['http_code' => $http_code, 'error_message' => $error_msg]);
    }
    
    // Parse API response
    $response_data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError(500, 'שגיאה בפענוח תגובת API', json_last_error_msg());
    }
    
    if (!isset($response_data['choices'][0]['message']['content'])) {
        sendError(500, 'תגובת API לא תקינה', 'Missing choices[0].message.content in response');
    }
    
    $summary = trim($response_data['choices'][0]['message']['content']);
    
    // Clean up summary
    $summary = preg_replace('/```json\s*/', '', $summary);
    $summary = preg_replace('/```\s*/', '', $summary);
    $summary = trim($summary);
    
    // Save summary to database
    $updateQuery = "UPDATE ranking_urls SET short_summary = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    
    if (!$updateStmt) {
        throw new Exception("Failed to prepare update query: " . $conn->error);
    }
    
    $updateStmt->bind_param("si", $summary, $id);
    
    if (!$updateStmt->execute()) {
        throw new Exception("Failed to execute update: " . $updateStmt->error);
    }
    
    $updateStmt->close();
    $stmt->close();
    closeDbConnection($conn);
    
    // Success!
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'סיכום נוצר בהצלחה',
        'summary' => $summary
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    sendError(500, 'שגיאה ביצירת סיכום', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} finally {
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}


