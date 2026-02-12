<?php
/**
 * Script to improve organization names in Hebrew using AI
 * Updates organization_name column with better Hebrew names
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/prompt_loader.php';

// Load API key if not already loaded
if (!defined('OPENAI_API_KEY')) {
    $api_key_file = __DIR__ . '/../config/api_key.php';
    if (file_exists($api_key_file)) {
        require_once $api_key_file;
    }
}

function getPageContent($url) {
    if (!$url) return null;
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$content) {
        return null;
    }
    
    // Extract text content (remove HTML tags)
    $content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);
    $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);
    $content = strip_tags($content);
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);
    
    return mb_substr($content, 0, 3000); // Limit to 3000 characters
}

function improveOrganizationNameWithAI($url, $current_name = null, $organization_type = null) {
    // Check for API key
    if (!defined('OPENAI_API_KEY')) {
        throw new Exception('OpenAI API key לא מוגדר - אנא הגדר OPENAI_API_KEY ב-config.php או api_key.php');
    }
    
    $api_key = OPENAI_API_KEY;
    if (empty($api_key) || $api_key === 'your-api-key-here') {
        throw new Exception('OpenAI API key לא מוגדר כראוי - אנא הגדר את OPENAI_API_KEY ב-config.php או api_key.php');
    }
    
    $api_url = 'https://api.openai.com/v1/chat/completions';
    
    // Load prompts
    try {
        $system_prompt = loadSystemMessage('improve_organization_name_hebrew');
        if (!$system_prompt) {
            $system_prompt = 'אתה עוזר לשפר שמות ארגונים בעברית. החזר רק את השם בעברית ללא הסברים.';
        }
    } catch (Exception $e) {
        error_log("Error loading system prompt: " . $e->getMessage());
        $system_prompt = 'אתה עוזר לשפר שמות ארגונים בעברית. החזר רק את השם בעברית ללא הסברים.';
    }
    
    // Build user prompt with context
    $user_prompt = "URL: $url\n";
    if ($current_name) {
        $user_prompt .= "שם קיים: $current_name\n";
    } else {
        $user_prompt .= "שם קיים: (ללא שם)\n";
    }
    if ($organization_type) {
        $user_prompt .= "סוג ארגון: $organization_type\n";
    }
    
    // Try to get page content for better context (skip if fails)
    try {
        $page_content = getPageContent($url);
        if ($page_content && strlen($page_content) > 10) {
            $user_prompt .= "\nתוכן הדף (חלק):\n" . mb_substr($page_content, 0, 1000) . "\n";
        }
    } catch (Exception $e) {
        // Skip page content if fails - not critical
        error_log("Could not fetch page content for $url: " . $e->getMessage());
    }
    
    // Load the main prompt and append context
    try {
        $main_prompt = loadPrompt('improve_organization_name_hebrew');
        $user_prompt = $main_prompt . "\n\n" . $user_prompt;
    } catch (Exception $e) {
        // If prompt file doesn't exist, use default
        error_log("Error loading prompt file: " . $e->getMessage());
        $user_prompt = "שפר את שם הארגון בעברית על סמך ה-URL והשם הקיים. אם זה ויקיפדיה - החזר 'ויקיפדיה'. אם זה עירייה - החזר 'עיריית [שם]'. החזר רק את השם בעברית.\n\n" . $user_prompt;
    }
    
    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => $system_prompt
            ],
            [
                'role' => 'user',
                'content' => $user_prompt
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 100
    ];
    
    $ch = curl_init($api_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        throw new Exception("שגיאת cURL: $curl_error");
    }
    
    if ($httpCode !== 200) {
        throw new Exception("שגיאת API: HTTP $httpCode - $response");
    }
    
    $response_data = json_decode($response, true);
    if (!$response_data) {
        error_log("Failed to decode API response: " . substr($response, 0, 500));
        throw new Exception("שגיאה בפענוח תגובת API: " . json_last_error_msg());
    }
    
    if (!isset($response_data['choices']) || !is_array($response_data['choices']) || empty($response_data['choices'])) {
        error_log("Invalid API response structure - no choices: " . json_encode($response_data));
        throw new Exception("תגובת API לא תקינה: אין choices בתגובה");
    }
    
    if (!isset($response_data['choices'][0]['message']['content'])) {
        $error_info = isset($response_data['error']) ? json_encode($response_data['error']) : 'Unknown error';
        error_log("Invalid API response - no content: " . $error_info);
        throw new Exception("תגובת API לא תקינה: " . (isset($response_data['error']['message']) ? $response_data['error']['message'] : 'אין תוכן בתגובה'));
    }
    
    $improved_name = trim($response_data['choices'][0]['message']['content']);
    
    // Validate we got something
    if (empty($improved_name)) {
        throw new Exception("התגובה מהאAI ריקה");
    }
    
    // Clean up the response - remove quotes, extra whitespace, etc.
    $improved_name = preg_replace('/^["\']+|["\']+$/', '', $improved_name);
    $improved_name = trim($improved_name);
    
    // If response is too long or contains multiple lines, take first line
    $lines = explode("\n", $improved_name);
    $improved_name = trim($lines[0]);
    
    // Remove any JSON wrapper if present
    if (preg_match('/^\s*\{[^}]*"name"[^:]*:\s*"([^"]+)"', $improved_name, $matches)) {
        $improved_name = $matches[1];
    }
    
    // Limit length
    if (mb_strlen($improved_name) > 255) {
        $improved_name = mb_substr($improved_name, 0, 255);
    }
    
    // Final validation
    if (empty($improved_name) || mb_strlen($improved_name) < 1) {
        throw new Exception("השם המשופר ריק לאחר עיבוד");
    }
    
    return $improved_name;
}

function improveOrganizationNamesHebrew($limit = null, $only_non_hebrew = true) {
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception('שגיאת חיבור למסד הנתונים');
    }
    
    // Build query
    $where = [];
    
    if ($only_non_hebrew) {
        // Filter for records where organization_name doesn't contain Hebrew characters
        // or contains English/Wikipedia patterns that should be improved
        $where[] = "(organization_name IS NOT NULL AND organization_name != '')";
        $where[] = "(organization_name LIKE '%wikipedia%' OR organization_name LIKE '%Wikipedia%' OR organization_name LIKE '%wiki%' OR organization_name REGEXP '[a-zA-Z]' OR organization_name NOT REGEXP '[א-ת]')";
    } else {
        $where[] = "organization_name IS NOT NULL AND organization_name != ''";
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "SELECT id, url, organization_name, organization_type 
              FROM ranking_urls 
              $where_clause
              ORDER BY id";
    
    if ($limit !== null && $limit > 0) {
        $query .= " LIMIT " . (int)$limit;
    }
    
    $result = $conn->query($query);
    
    if (!$result) {
        closeDbConnection($conn);
        throw new Exception('שגיאה בשאילתת ranking_urls: ' . $conn->error);
    }
    
    $stats = [
        'total' => $result->num_rows,
        'processed' => 0,
        'updated' => 0,
        'errors' => 0,
        'skipped' => 0
    ];
    
    $update_stmt = $conn->prepare("UPDATE ranking_urls 
                                   SET organization_name = ? 
                                   WHERE id = ?");
    
    if (!$update_stmt) {
        closeDbConnection($conn);
        throw new Exception('שגיאה בהכנת שאילתת UPDATE: ' . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $stats['processed']++;
        
        try {
            $improved_name = improveOrganizationNameWithAI(
                $row['url'],
                $row['organization_name'],
                $row['organization_type']
            );
            
            if ($improved_name && $improved_name !== $row['organization_name']) {
                $update_stmt->bind_param("si", $improved_name, $row['id']);
                
                if ($update_stmt->execute()) {
                    $stats['updated']++;
                    if (php_sapi_name() === 'cli') {
                        echo "✓ ID {$row['id']}: '{$row['organization_name']}' → '$improved_name'\n";
                    }
                } else {
                    $stats['errors']++;
                    error_log("Error updating ID {$row['id']}: " . $update_stmt->error);
                }
            } else {
                $stats['skipped']++;
            }
            
            // Rate limiting - wait 1 second between API calls
            sleep(1);
            
        } catch (Exception $e) {
            $stats['errors']++;
            $error_msg = $e->getMessage();
            $error_log = "Error processing ID {$row['id']} (URL: {$row['url']}, Current name: {$row['organization_name']}): " . $error_msg;
            error_log($error_log);
            if (php_sapi_name() === 'cli') {
                echo "✗ ID {$row['id']}: " . $error_msg . "\n";
            }
            
            // If this is the first error and we're in web context, log detailed info
            if ($stats['errors'] === 1 && php_sapi_name() !== 'cli') {
                error_log("First error details - URL: {$row['url']}, Name: {$row['organization_name']}, Type: {$row['organization_type']}");
            }
        }
    }
    
    $update_stmt->close();
    closeDbConnection($conn);
    
    return $stats;
}

