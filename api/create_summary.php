<?php
/**
 * API endpoint for creating summaries using GPT-4o
 * Enhanced with detailed error logging and better error messages
 */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, but log them
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

// Initial checks - before headers
// Check PHP version
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
    /**
     * Polyfill for str_starts_with() - available in PHP 8.0+
     * @param string $haystack The string to search in
     * @param string $needle The string to search for
     * @return bool
     */
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
    ob_clean(); // Clear any output before sending JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    logError("=== CREATE SUMMARY REQUEST START ===");
    
    // Step 1: Load required files
    try {
        require_once '../config/database.php';
        logError("✓ Database config loaded");
    } catch (Exception $e) {
        logError("✗ Failed to load database config", ['error' => $e->getMessage()]);
        sendError(500, 'שגיאה בטעינת הגדרות מסד הנתונים', $e->getMessage());
    }
    
    try {
        require_once '../config/api_key.php';
        logError("✓ API key config loaded");
    } catch (Exception $e) {
        logError("✗ Failed to load API key config", ['error' => $e->getMessage()]);
        sendError(500, 'שגיאה בטעינת מפתח API', $e->getMessage());
    }
    
    try {
        require_once '../lib/url_fetcher.php';
        logError("✓ URL fetcher loaded");
    } catch (Exception $e) {
        logError("✗ Failed to load URL fetcher", ['error' => $e->getMessage()]);
        sendError(500, 'שגיאה בטעינת ספריית URL', $e->getMessage());
    }
    
    // Step 2: Get JSON input
    $rawInput = file_get_contents('php://input');
    logError("Raw input received", ['length' => strlen($rawInput), 'preview' => substr($rawInput, 0, 100)]);
    
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logError("✗ JSON decode error", ['error' => json_last_error_msg(), 'input' => $rawInput]);
        sendError(400, 'שגיאה בפורמט JSON', json_last_error_msg());
    }
    
    if (!isset($input['id']) || empty($input['id'])) {
        logError("✗ Missing ID in request", ['input' => $input]);
        sendError(400, 'מזהה רשומה נדרש');
    }
    
    $id = (int)$input['id'];
    logError("Processing request for record ID", ['id' => $id]);
    
    // Step 3: Database connection
    try {
        $conn = getDbConnection();
        if (!$conn) {
            throw new Exception("Database connection returned null");
        }
        logError("✓ Database connection established");
    } catch (Exception $e) {
        logError("✗ Database connection failed", ['error' => $e->getMessage()]);
        sendError(500, 'שגיאה בחיבור למסד הנתונים', $e->getMessage());
    }
    
    // Step 4: Get record data
    $query = "SELECT 
        id, url, source_type, year, organization_type, jurisdiction_level,
        geographic_scope, topic_category, document_type, target_audience,
        values_orientation, cultural_focus, zionism_references, identity_theme,
        historical_periods, language, accessibility_level, publication_format,
        period_referenced, temporal_scope, completeness, reliability_indicators
    FROM ranking_urls 
    WHERE id = ?";
    
    try {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare query: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$row = $result->fetch_assoc()) {
            logError("✗ Record not found", ['id' => $id]);
            sendError(404, 'רשומה לא נמצאה', "Record ID $id does not exist");
        }
        
        logError("✓ Record found", ['id' => $id, 'url' => $row['url']]);
    } catch (Exception $e) {
        logError("✗ Database query failed", ['error' => $e->getMessage(), 'query' => $query]);
        sendError(500, 'שגיאה בקבלת נתוני הרשומה', $e->getMessage());
    }
    
    $url = $row['url'];
    
    // Step 5: Fetch URL content
    $urlContent = '';
    try {
        logError("Fetching URL content", ['url' => $url]);
        $urlContent = fetchWebpageContent($url);
        logError("✓ URL content fetched", ['length' => strlen($urlContent)]);
    } catch (Exception $e) {
        logError("⚠ URL fetch failed, continuing with metadata only", ['error' => $e->getMessage()]);
        $urlContent = "[לא ניתן למשוך תוכן מהקישור: " . $e->getMessage() . "]";
    }
    
    // Step 6: Build metadata summary string
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
    logError("Metadata string built", ['length' => strlen($metadataString), 'parts_count' => count($metadataParts)]);
    
    // Step 7: Build prompt for GPT-4o
    $prompt = "על בסיס התוכן הבא והמטא-דאטה הקיימת, צור סיכום קצר בעברית (2-3 משפטים) של המסמך.\n";
    $prompt .= "התמקד בנושא המרכזי, נושאי מפתח, והרלוונטיות לזהות ישראלית וציונות.\n\n";
    $prompt .= "URL: $url\n\n";
    
    if (!empty($urlContent) && !str_starts_with($urlContent, '[')) {
        // Use mb_substr if available, fallback to substr for compatibility
        if (function_exists('mb_substr')) {
            $prompt .= "תוכן הדף:\n" . mb_substr($urlContent, 0, 5000) . "\n\n";
        } else {
            // Fallback to regular substr if mbstring not available
            $prompt .= "תוכן הדף:\n" . substr($urlContent, 0, 5000) . "\n\n";
        }
    }
    
    if (!empty($metadataString)) {
        $prompt .= "מטא-דאטה קיימת:\n$metadataString\n\n";
    }
    
    $prompt .= "צור סיכום קצר ומדויק בעברית (2-3 משפטים בלבד).";
    
    logError("Prompt built", ['length' => strlen($prompt)]);
    
    // Step 8: Check API key
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) {
        logError("✗ OpenAI API key not defined");
        sendError(500, 'מפתח API לא מוגדר', 'OPENAI_API_KEY constant is not defined or empty');
    }
    
    $api_key = OPENAI_API_KEY;
    $api_url = 'https://api.openai.com/v1/chat/completions';
    
    // Step 9: Prepare API request
    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'אתה עוזר מקצועי ליצירת סיכומים בעברית. תמיד החזר סיכום קצר ומדויק בעברית בלבד, ללא הסברים נוספים.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.5,
        'max_tokens' => 500
    ];
    
    logError("Calling OpenAI API", ['model' => $data['model'], 'url' => $api_url]);
    
    // Step 10: Call GPT-4o API
    $ch = curl_init();
    
    if (!$ch) {
        logError("✗ Failed to initialize cURL");
        sendError(500, 'שגיאה באתחול cURL', 'curl_init() returned false');
    }
    
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
        logError("✗ cURL error", ['errno' => $curl_errno, 'error' => $curl_error]);
        sendError(500, 'שגיאה בחיבור ל-OpenAI API', "cURL error ($curl_errno): $curl_error");
    }
    
    if ($http_code !== 200) {
        $error_data = json_decode($response, true);
        $error_msg = isset($error_data['error']['message']) ? $error_data['error']['message'] : "HTTP error: $http_code";
        $error_type = isset($error_data['error']['type']) ? $error_data['error']['type'] : 'unknown';
        $error_code = isset($error_data['error']['code']) ? $error_data['error']['code'] : $http_code;
        
        logError("✗ OpenAI API error", [
            'http_code' => $http_code,
            'error_type' => $error_type,
            'error_code' => $error_code,
            'error_message' => $error_msg,
            'response' => substr($response, 0, 500)
        ]);
        
        sendError(500, 'שגיאה ב-OpenAI API', [
            'http_code' => $http_code,
            'error_type' => $error_type,
            'error_code' => $error_code,
            'error_message' => $error_msg
        ]);
    }
    
    logError("✓ OpenAI API response received", ['http_code' => $http_code, 'response_length' => strlen($response)]);
    
    // Step 11: Parse API response
    $response_data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logError("✗ Failed to parse API response", ['error' => json_last_error_msg(), 'response_preview' => substr($response, 0, 500)]);
        sendError(500, 'שגיאה בפענוח תגובת API', json_last_error_msg());
    }
    
    if (!isset($response_data['choices'][0]['message']['content'])) {
        logError("✗ Invalid API response structure", ['response' => json_encode($response_data, JSON_UNESCAPED_UNICODE)]);
        sendError(500, 'תגובת API לא תקינה', 'Missing choices[0].message.content in response');
    }
    
    $summary = trim($response_data['choices'][0]['message']['content']);
    
    // Clean up summary (remove markdown code blocks if present)
    $summary = preg_replace('/```json\s*/', '', $summary);
    $summary = preg_replace('/```\s*/', '', $summary);
    $summary = trim($summary);
    
    logError("✓ Summary generated", ['length' => strlen($summary), 'preview' => substr($summary, 0, 100)]);
    
    // Step 12: Save summary to database
    try {
        $updateQuery = "UPDATE ranking_urls SET short_summary = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        if (!$updateStmt) {
            throw new Exception("Failed to prepare update query: " . $conn->error);
        }
        
        $updateStmt->bind_param("si", $summary, $id);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to execute update: " . $updateStmt->error);
        }
        
        logError("✓ Summary saved to database", ['id' => $id]);
        $updateStmt->close();
    } catch (Exception $e) {
        logError("✗ Failed to save summary", ['error' => $e->getMessage()]);
        sendError(500, 'שגיאה בשמירת הסיכום', $e->getMessage());
    }
    
    // Success!
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'סיכום נוצר בהצלחה',
        'summary' => $summary
    ], JSON_UNESCAPED_UNICODE);
    
    logError("=== CREATE SUMMARY REQUEST SUCCESS ===");
    
} catch (Exception $e) {
    logError("✗ UNEXPECTED EXCEPTION", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    sendError(500, 'שגיאה ביצירת סיכום', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    logError("✗ FATAL ERROR", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    sendError(500, 'שגיאה קריטית ביצירת סיכום', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} finally {
    // Cleanup with error handling
    try {
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    } catch (Exception $e) {
        // Only log, don't throw
        error_log("Error closing statement: " . $e->getMessage());
    } catch (Error $e) {
        error_log("Fatal error closing statement: " . $e->getMessage());
    }
    
    try {
        if (isset($conn) && $conn instanceof mysqli) {
            closeDbConnection($conn);
        }
    } catch (Exception $e) {
        error_log("Error closing connection: " . $e->getMessage());
    } catch (Error $e) {
        error_log("Fatal error closing connection: " . $e->getMessage());
    }
    
    // Clean output buffer safely
    if (ob_get_level() > 0) {
        try {
            ob_end_flush();
        } catch (Exception $e) {
            error_log("Error flushing output buffer: " . $e->getMessage());
        }
    }
}
