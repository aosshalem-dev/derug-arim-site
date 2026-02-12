<?php
/**
 * API endpoint for retrying failed metadata extraction with GPT-4o
 * Includes failure explanation if extraction fails
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';
require_once '../config/api_key.php';
require_once '../lib/url_fetcher.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'מזהה רשומה נדרש'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = (int)$input['id'];
$conn = getDbConnection();

// Get record URL
$query = "SELECT id, url FROM ranking_urls WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    http_response_code(404);
    echo json_encode(['error' => 'רשומה לא נמצאה'], JSON_UNESCAPED_UNICODE);
    exit;
}

$url = $row['url'];
$stmt->close();

try {
    // Parse URL
    $url_info = parse_url($url);
    $domain = isset($url_info['host']) ? $url_info['host'] : '';
    $path = isset($url_info['path']) ? $url_info['path'] : '';
    
    // Fetch URL content
    $content = '';
    try {
        $content = fetchWebpageContent($url);
    } catch (Exception $e) {
        $content = "[Error fetching content: " . $e->getMessage() . "]";
    }
    
    // Get existing content types for context
    $existing_types_query = "SELECT DISTINCT content_type FROM ranking_urls WHERE content_type IS NOT NULL AND content_type != '' LIMIT 50";
    $existing_types_result = $conn->query($existing_types_query);
    $existing_content_types = [];
    if ($existing_types_result) {
        while ($type_row = $existing_types_result->fetch_assoc()) {
            $existing_content_types[] = $type_row['content_type'];
        }
    }
    
    // Extract metadata using GPT-4o
    $metadata = extractMetadataWithOpenAI($url, $domain, $path, $content, $existing_content_types);
    
    if ($metadata && isset($metadata['source_type'])) {
        // Success - update database
        $updateQuery = "UPDATE ranking_urls SET
            source_type = ?,
            year = ?,
            organization_type = ?,
            jurisdiction_level = ?,
            geographic_scope = ?,
            topic_category = ?,
            document_type = ?,
            target_audience = ?,
            content_type = ?,
            values_orientation = ?,
            cultural_focus = ?,
            zionism_references = ?,
            identity_theme = ?,
            historical_periods = ?,
            language = ?,
            accessibility_level = ?,
            publication_format = ?,
            period_referenced = ?,
            temporal_scope = ?,
            completeness = ?,
            reliability_indicators = ?,
            metadata_extracted_at = NOW(),
            metadata_status = 'extracted',
            failure_reason = NULL
        WHERE id = ?";
        
        $updateStmt = $conn->prepare($updateQuery);
        
        $source_type = $metadata['source_type'] ?? null;
        $year = isset($metadata['year']) && $metadata['year'] ? (int)$metadata['year'] : null;
        $organization_type = $metadata['organization_type'] ?? null;
        $jurisdiction_level = $metadata['jurisdiction_level'] ?? null;
        $geographic_scope = $metadata['geographic_scope'] ?? null;
        $topic_category = $metadata['topic_category'] ?? null;
        $document_type = $metadata['document_type'] ?? null;
        $target_audience = $metadata['target_audience'] ?? null;
        $content_type = $metadata['content_type'] ?? null;
        $values_orientation = isset($metadata['values_orientation']) ? json_encode($metadata['values_orientation'], JSON_UNESCAPED_UNICODE) : null;
        $cultural_focus = $metadata['cultural_focus'] ?? null;
        $zionism_references = $metadata['zionism_references'] ?? null;
        $identity_theme = isset($metadata['identity_theme']) ? json_encode($metadata['identity_theme'], JSON_UNESCAPED_UNICODE) : null;
        $historical_periods = isset($metadata['historical_periods']) ? json_encode($metadata['historical_periods'], JSON_UNESCAPED_UNICODE) : null;
        $language = $metadata['language'] ?? null;
        $accessibility_level = $metadata['accessibility_level'] ?? null;
        $publication_format = $metadata['publication_format'] ?? null;
        $period_referenced = $metadata['period_referenced'] ?? null;
        $temporal_scope = $metadata['temporal_scope'] ?? null;
        $completeness = $metadata['completeness'] ?? null;
        $reliability_indicators = isset($metadata['reliability_indicators']) ? json_encode($metadata['reliability_indicators'], JSON_UNESCAPED_UNICODE) : null;
        
        $updateStmt->bind_param("sisssssssssssssssssssssi",
            $source_type, $year, $organization_type, $jurisdiction_level, $geographic_scope,
            $topic_category, $document_type, $target_audience, $content_type, $values_orientation, $cultural_focus,
            $zionism_references, $identity_theme, $historical_periods, $language, $accessibility_level,
            $publication_format, $period_referenced, $temporal_scope, $completeness, $reliability_indicators,
            $id
        );
        
        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'מטא-דאטה חולצה בהצלחה',
                'metadata' => $metadata
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception("שגיאה בעדכון המסד נתונים: " . $updateStmt->error);
        }
        
        $updateStmt->close();
        
    } else {
        // Failed - get explanation
        $failureReason = explainFailure($url, $content, "Failed to extract metadata - missing source_type");
        
        // Update database with failure status and reason
        $updateQuery = "UPDATE ranking_urls SET 
            metadata_status = 'failed',
            failure_reason = ?,
            metadata_extracted_at = NOW()
        WHERE id = ?";
        
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $failureReason, $id);
        $updateStmt->execute();
        $updateStmt->close();
        
        echo json_encode([
            'success' => false,
            'message' => 'חילוץ מטא-דאטה נכשל',
            'failure_reason' => $failureReason
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // Get failure explanation
    $failureReason = explainFailure($url, $content ?? '', $e->getMessage());
    
    // Update database with failure status and reason
    $updateQuery = "UPDATE ranking_urls SET 
        metadata_status = 'failed',
        failure_reason = ?,
        metadata_extracted_at = NOW()
    WHERE id = ?";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $failureReason, $id);
    $updateStmt->execute();
    $updateStmt->close();
    
    http_response_code(500);
    echo json_encode([
        'error' => 'שגיאה בחילוץ מטא-דאטה',
        'failure_reason' => $failureReason
    ], JSON_UNESCAPED_UNICODE);
}

closeDbConnection($conn);

/**
 * Extract metadata using GPT-4o
 */
function extractMetadataWithOpenAI($url, $domain, $path, $content, $existing_content_types = []) {
    $api_key = OPENAI_API_KEY;
    $api_url = 'https://api.openai.com/v1/chat/completions';
    
    // Build comprehensive prompt (similar to extract_metadata.php but using GPT-4o)
    $prompt = "אתה עוזר מקצועי לחילוץ מטא-דאטה תיאורית ממסמכים ומקורות מידע.\n\n";
    $prompt .= "קישור לניתוח:\n";
    $prompt .= "URL מלא: $url\n";
    $prompt .= "דומיין: $domain\n";
    $prompt .= "נתיב: $path\n\n";
    
    if (!empty($content) && !str_starts_with($content, '[') && !str_starts_with($content, '[PDF')) {
        $prompt .= "תוכן הדף (חלק):\n" . mb_substr($content, 0, 5000) . "\n\n";
    } else {
        $prompt .= "הערה: התוכן הוא קובץ בינארי (PDF או אחר) ולא ניתן לקרוא אותו ישירות.\n\n";
    }
    
    $prompt .= "אנא חלץ את כל השדות הבאים בפורמט JSON. חשוב: השתמש בתיאורים אובייקטיביים בלבד, לא בהערכות שיפוטיות.\n\n";
    
    $prompt .= "שדות בסיסיים:\n";
    $prompt .= "- source_type: סוג המקור (municipality, government_agency, media, educational_institution, ngo, research_institution, other)\n";
    $prompt .= "- year: שנה רלוונטית (מספר שלם או null)\n\n";
    
    $prompt .= "הקשר מוסדי (Institutional Context):\n";
    $prompt .= "- organization_type: סוג הארגון (municipality, government_agency, media, educational_institution, ngo, research_institution, other)\n";
    $prompt .= "- jurisdiction_level: רמת סמכות שיפוט (local, regional, national, international)\n";
    $prompt .= "- geographic_scope: היקף גיאוגרפי - שם עיר/אזור (מחרוזת או null)\n\n";
    
    $prompt .= "תחום תוכן (Content Domain):\n";
    $prompt .= "- topic_category: קטגוריית נושא (education, culture, policy, news, research, heritage, community, other)\n";
    $prompt .= "- document_type: סוג מסמך (report, article, policy_document, curriculum, announcement, protocol, plan, other)\n";
    $prompt .= "- target_audience: קהל יעד (general_public, educators, students, policymakers, researchers, community_leaders, other)\n";
    $prompt .= "- content_type: סוג תוכן ספציפי (מחרוזת תיאורית, למשל: \"תכנית חינוכית\", \"פרוטוקול ישיבה\", \"דוח שנתי\")\n";
    
    if (!empty($existing_content_types)) {
        $prompt .= "\nקטגוריות תוכן קיימות במערכת (השתמש באותה קטגוריה אם התוכן דומה):\n";
        foreach ($existing_content_types as $type) {
            $prompt .= "  - \"$type\"\n";
        }
        $prompt .= "\nחשוב: אם התוכן דומה לאחת הקטגוריות הקיימות, השתמש באותה קטגוריה בדיוק (case-sensitive).\n";
        $prompt .= "אם התוכן שונה, צור קטגוריה חדשה תיאורית.\n\n";
    }
    
    $prompt .= "אינדיקטורים אידיאולוגיים (תיאוריים בלבד):\n";
    $prompt .= "- values_orientation: מערך ערכים המופיעים בתוכן (למשל: [\"collective_identity\", \"tradition\", \"innovation\"] או [])\n";
    $prompt .= "- cultural_focus: מוקד תרבותי (hebrew_culture, jewish_heritage, israeli_identity, multicultural, universal, mixed, unclear)\n\n";
    
    $prompt .= "זהות וציונות (תיאור נוכחות, לא הערכה):\n";
    $prompt .= "- zionism_references: התייחסויות לציונות (explicit, implicit, none, unclear)\n";
    $prompt .= "- identity_theme: נושאי זהות המופיעים (מערך, למשל: [\"national_identity\", \"cultural_identity\"] או [])\n";
    $prompt .= "- historical_periods: תקופות היסטוריות המוזכרות (מערך, למשל: [\"pre-state\", \"independence\"] או [])\n\n";
    
    $prompt .= "שקיפות ושפה:\n";
    $prompt .= "- language: שפת התוכן (hebrew, english, arabic, mixed, other)\n";
    $prompt .= "- accessibility_level: רמת נגישות (public, restricted, unclear)\n";
    $prompt .= "- publication_format: פורמט פרסום (pdf, html, text, image, video, other)\n\n";
    
    $prompt .= "סמנים זמניים/היסטוריים:\n";
    $prompt .= "- period_referenced: תקופה מוזכרת (מחרוזת, למשל: \"2000s\", \"pre-state\" או null)\n";
    $prompt .= "- temporal_scope: היקף זמני (current, historical, future, mixed)\n\n";
    
    $prompt .= "איכות מקור:\n";
    $prompt .= "- completeness: שלמות (full_document, excerpt, summary, unclear)\n";
    $prompt .= "- reliability_indicators: אינדיקטורי אמינות (מערך, למשל: [\"official_source\", \"archived\"] או [])\n\n";
    
    $prompt .= "השב בפורמט JSON בלבד עם כל השדות לעיל.\n";
    $prompt .= "השתמש ב-null עבור שדות שלא ניתן לזהות.\n";
    $prompt .= "לשדות מערך (JSON arrays), החזר מערך ריק [] אם אין מידע.\n";
    
    // API call with GPT-4o
    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'אתה עוזר מקצועי לחילוץ מטא-דאטה מקישורים. תמיד החזר תשובה בפורמט JSON בלבד.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 1500
    ];
    
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
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        throw new Exception("cURL error in API call: $error");
    }
    
    if ($http_code !== 200) {
        $error_data = json_decode($response, true);
        $error_msg = isset($error_data['error']['message']) ? $error_data['error']['message'] : "HTTP error: $http_code";
        throw new Exception("OpenAI API error: $error_msg");
    }
    
    $response_data = json_decode($response, true);
    
    if (!isset($response_data['choices'][0]['message']['content'])) {
        throw new Exception("Invalid API response structure");
    }
    
    $api_content = $response_data['choices'][0]['message']['content'];
    
    // Clean up content
    $api_content = preg_replace('/```json\s*/', '', $api_content);
    $api_content = preg_replace('/```\s*/', '', $api_content);
    $api_content = trim($api_content);
    
    // Parse JSON
    $metadata = json_decode($api_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse JSON response: " . json_last_error_msg());
    }
    
    if (!isset($metadata['source_type'])) {
        throw new Exception("Missing source_type in response");
    }
    
    // Ensure all required fields exist
    $required_fields = [
        'source_type', 'year', 'organization_type', 'jurisdiction_level', 'geographic_scope',
        'topic_category', 'document_type', 'target_audience', 'content_type', 'values_orientation', 'cultural_focus',
        'zionism_references', 'identity_theme', 'historical_periods', 'language', 'accessibility_level',
        'publication_format', 'period_referenced', 'temporal_scope', 'completeness', 'reliability_indicators'
    ];
    
    foreach ($required_fields as $field) {
        if (!isset($metadata[$field])) {
            $metadata[$field] = null;
        }
    }
    
    return $metadata;
}

/**
 * Explain failure reason using GPT-4o
 */
function explainFailure($url, $content, $errorMessage) {
    $api_key = OPENAI_API_KEY;
    $api_url = 'https://api.openai.com/v1/chat/completions';
    
    $prompt = "הסבר בעברית מדוע נכשל חילוץ המטא-דאטה מהקישור הבא:\n\n";
    $prompt .= "URL: $url\n";
    $prompt .= "שגיאה: $errorMessage\n\n";
    
    if (!empty($content) && !str_starts_with($content, '[')) {
        $prompt .= "תוכן שנשלף (חלק):\n" . mb_substr($content, 0, 2000) . "\n\n";
    } else {
        $prompt .= "תוכן שנשלף: $content\n\n";
    }
    
    $prompt .= "הסבר את הסיבה לכשלון בצורה ברורה ומקצועית בעברית (2-3 משפטים).";
    
    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'אתה עוזר מקצועי לניתוח שגיאות. תמיד החזר הסבר בעברית בלבד.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.5,
        'max_tokens' => 300
    ];
    
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
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error || $http_code !== 200) {
        // Fallback explanation if API fails
        return "לא ניתן לחלץ מטא-דאטה מהקישור. שגיאה: $errorMessage";
    }
    
    $response_data = json_decode($response, true);
    
    if (isset($response_data['choices'][0]['message']['content'])) {
        return trim($response_data['choices'][0]['message']['content']);
    }
    
    return "לא ניתן לחלץ מטא-דאטה מהקישור. שגיאה: $errorMessage";
}


