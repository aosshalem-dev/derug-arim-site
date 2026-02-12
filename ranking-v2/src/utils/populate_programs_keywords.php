<?php
/**
 * Populate keywords column in educational_programs table using AI
 * Extracts relevant keywords like SEL, מגוון, חוסן נפשי, etc.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/migrate_add_keywords_to_programs.php';

// Load API key
$api_key_paths = [
    __DIR__ . '/../config/api_key.php',
    __DIR__ . '/../../config/api_key.php',
    __DIR__ . '/../../../config/api_key.php'
];

$api_key_loaded = false;
foreach ($api_key_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $api_key_loaded = true;
        break;
    }
}

if (!$api_key_loaded || !defined('OPENAI_API_KEY')) {
    throw new Exception('OpenAI API key not found. Please configure api_key.php');
}

function extractKeywordsWithAI($program_name, $description, $topic_category, $target_audience) {
    $api_key = OPENAI_API_KEY;
    $api_url = 'https://api.openai.com/v1/chat/completions';
    
    $prompt = "אתה עוזר מקצועי לזיהוי מילות מפתח רלוונטיות לתוכניות חינוכיות.\n\n";
    $prompt .= "שם התוכנית: $program_name\n";
    if ($description) {
        $prompt .= "תיאור: " . mb_substr($description, 0, 1000) . "\n";
    }
    if ($topic_category) {
        $prompt .= "קטגוריה: $topic_category\n";
    }
    if ($target_audience) {
        $prompt .= "קהל יעד: $target_audience\n";
    }
    
    $prompt .= "\nאנא זהה מילות מפתח רלוונטיות לתוכנית זו. מילות מפתח יכולות לכלול:\n";
    $prompt .= "- מושגים חינוכיים: SEL (למידה חברתית-רגשית), חוסן נפשי, גמישות מחשבתית\n";
    $prompt .= "- ערכים: מגוון, הכלה, שוויון, כבוד הדדי\n";
    $prompt .= "- תחומי תוכן: זהות, ציונות, מורשת, תרבות, היסטוריה\n";
    $prompt .= "- מיומנויות: חשיבה ביקורתית, עבודת צוות, מנהיגות\n";
    $prompt .= "- גישות: למידה חווייתית, למידה מבוססת פרויקטים\n\n";
    
    $prompt .= "החזר JSON עם מערך של מילות מפתח:\n";
    $prompt .= "{\n";
    $prompt .= "  \"keywords\": [\"מילת מפתח 1\", \"מילת מפתח 2\", ...]\n";
    $prompt .= "}\n\n";
    $prompt .= "החזר רק מילות מפתח רלוונטיות וספציפיות. אם אין מילות מפתח רלוונטיות, החזר מערך ריק.\n";
    $prompt .= "החזר רק JSON, ללא טקסט נוסף.";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a professional assistant for identifying relevant keywords for educational programs. Return only valid JSON.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 500
    ]));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception("OpenAI API error: HTTP $http_code");
    }
    
    $response_data = json_decode($response, true);
    if (!isset($response_data['choices'][0]['message']['content'])) {
        throw new Exception("Invalid API response structure");
    }
    
    $content = $response_data['choices'][0]['message']['content'];
    $content = preg_replace('/```json\s*/', '', $content);
    $content = preg_replace('/```\s*/', '', $content);
    $content = trim($content);
    
    $result = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse JSON: " . json_last_error_msg());
    }
    
    return $result['keywords'] ?? [];
}

function populateProgramsKeywords($limit = null, $only_empty = true) {
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception('שגיאת חיבור למסד הנתונים');
    }
    
    // Ensure column exists
    try {
        addKeywordsColumnToPrograms($conn);
    } catch (Exception $e) {
        // Continue anyway
    }
    
    // Build query
    $where = $only_empty ? "WHERE keywords IS NULL OR keywords = ''" : "";
    $query = "SELECT program_id, program_name, description, topic_category, target_audience 
              FROM educational_programs 
              $where
              ORDER BY program_id";
    
    if ($limit !== null && $limit > 0) {
        $query .= " LIMIT " . (int)$limit;
    }
    
    $result = $conn->query($query);
    
    if (!$result) {
        closeDbConnection($conn);
        throw new Exception('שגיאה בשאילתת educational_programs: ' . $conn->error);
    }
    
    $stats = [
        'total' => 0,
        'processed' => 0,
        'updated' => 0,
        'errors' => 0
    ];
    
    $update_stmt = $conn->prepare("UPDATE educational_programs 
                                   SET keywords = ? 
                                   WHERE program_id = ?");
    
    if (!$update_stmt) {
        closeDbConnection($conn);
        throw new Exception('שגיאה בהכנת שאילתת UPDATE: ' . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $stats['total']++;
        
        try {
            // Extract keywords with AI
            $keywords = extractKeywordsWithAI(
                $row['program_name'],
                $row['description'],
                $row['topic_category'],
                $row['target_audience']
            );
            
            // Convert array to comma-separated string
            $keywords_str = !empty($keywords) ? implode(', ', $keywords) : null;
            
            // Update record
            $update_stmt->bind_param("si", $keywords_str, $row['program_id']);
            
            if ($update_stmt->execute()) {
                $stats['updated']++;
                $stats['processed']++;
            } else {
                $stats['errors']++;
                error_log("Error updating keywords for program {$row['program_id']}: " . $update_stmt->error);
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
            
        } catch (Exception $e) {
            $stats['errors']++;
            error_log("Error processing program {$row['program_id']}: " . $e->getMessage());
        }
    }
    
    $update_stmt->close();
    closeDbConnection($conn);
    
    return $stats;
}



