<?php
/**
 * Populate educational_programs table by extracting programs from ranking_urls
 * Uses AI to identify and extract educational programs from URLs
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/migrate_create_educational_programs_table.php';

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

function fetchWebpageContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 || !$content) {
        return null;
    }
    
    // Extract text from HTML
    $content = strip_tags($content);
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);
    
    // Limit content length
    if (mb_strlen($content) > 8000) {
        $content = mb_substr($content, 0, 8000) . '...';
    }
    
    return $content;
}

function extractEducationalProgramsWithAI($url, $content, $organization_name = null) {
    $api_key = OPENAI_API_KEY;
    $api_url = 'https://api.openai.com/v1/chat/completions';
    
    $prompt = "אתה עוזר מקצועי לחילוץ מידע על תוכניות חינוכיות ממסמכים ומקורות מידע.\n\n";
    $prompt .= "URL: $url\n";
    if ($organization_name) {
        $prompt .= "ארגון: $organization_name\n";
    }
    $prompt .= "\nתוכן הדף:\n" . mb_substr($content, 0, 6000) . "\n\n";
    
    $prompt .= "אנא זהה תוכניות חינוכיות בדף זה. תוכנית חינוכית יכולה להיות:\n";
    $prompt .= "- תכנית לימודים\n";
    $prompt .= "- סדנה או קורס\n";
    $prompt .= "- יוזמה חינוכית\n";
    $prompt .= "- משאב חינוכי\n";
    $prompt .= "- אירוע חינוכי\n";
    $prompt .= "- סמינר או הרצאה\n\n";
    
    $prompt .= "החזר JSON עם מערך של תוכניות. כל תוכנית צריכה לכלול:\n";
    $prompt .= "{\n";
    $prompt .= "  \"programs\": [\n";
    $prompt .= "    {\n";
    $prompt .= "      \"program_name\": \"שם התוכנית\",\n";
    $prompt .= "      \"description\": \"תיאור קצר של התוכנית\",\n";
    $prompt .= "      \"topic_category\": \"education/culture/policy/etc\",\n";
    $prompt .= "      \"target_audience\": \"students/educators/general_public/etc\",\n";
    $prompt .= "      \"age_range\": \"6-12 או 13-18 או adults או null\",\n";
    $prompt .= "      \"duration\": \"semester/year/ongoing/etc או null\",\n";
    $prompt .= "      \"location\": \"מיקום או null\",\n";
    $prompt .= "      \"program_type\": \"curriculum/workshop/course/seminar/event/resource/initiative/other\",\n";
    $prompt .= "      \"language\": \"hebrew/english/arabic/mixed/other\"\n";
    $prompt .= "    }\n";
    $prompt .= "  ]\n";
    $prompt .= "}\n\n";
    
    $prompt .= "אם אין תוכניות חינוכיות בדף, החזר {\"programs\": []}.\n";
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
                'content' => 'You are a professional assistant for extracting educational program information from web pages. Return only valid JSON.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 2000
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
    
    return $result['programs'] ?? [];
}

function populateEducationalProgramsTable($limit = null, $only_relevant = true) {
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception('שגיאת חיבור למסד הנתונים');
    }
    
    // Ensure table exists
    try {
        createEducationalProgramsTable($conn);
    } catch (Exception $e) {
        // Continue anyway
    }
    
    // Build query to get URLs that might have educational programs
    $where = "WHERE 1=1";
    if ($only_relevant) {
        // Focus on URLs with education-related categories or organization types
        $where .= " AND (
            topic_category LIKE '%education%' 
            OR topic_category LIKE '%חינוך%'
            OR organization_type IN ('educational_institution', 'municipality', 'ngo')
            OR short_summary LIKE '%חינוך%'
            OR short_summary LIKE '%education%'
            OR short_summary LIKE '%תוכנית%'
            OR short_summary LIKE '%program%'
        )";
    }
    
    $query = "SELECT id, url, organization_name, organization_type, topic_category, short_summary 
              FROM ranking_urls 
              $where
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
        'total_urls' => 0,
        'processed' => 0,
        'programs_found' => 0,
        'programs_inserted' => 0,
        'errors' => 0
    ];
    
    $insert_stmt = $conn->prepare("INSERT INTO educational_programs 
        (program_name, description, url_id, organization_id, topic_category, 
         target_audience, age_range, duration, location, program_type, language, 
         status, extracted_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unknown', NOW())");
    
    if (!$insert_stmt) {
        closeDbConnection($conn);
        throw new Exception('שגיאה בהכנת שאילתת INSERT: ' . $conn->error);
    }
    
    // Get organization IDs for matching
    $org_map = [];
    $org_result = $conn->query("SELECT org_id, org_name FROM organizations");
    if ($org_result) {
        while ($row = $org_result->fetch_assoc()) {
            $org_map[$row['org_name']] = $row['org_id'];
        }
    }
    
    while ($row = $result->fetch_assoc()) {
        $stats['total_urls']++;
        $url_id = $row['id'];
        $url = $row['url'];
        $org_name = $row['organization_name'];
        $org_id = isset($org_map[$org_name]) ? $org_map[$org_name] : null;
        
        try {
            // Fetch content
            $content = fetchWebpageContent($url);
            if (!$content) {
                continue; // Skip if can't fetch content
            }
            
            // Extract programs with AI
            $programs = extractEducationalProgramsWithAI($url, $content, $org_name);
            
            $stats['processed']++;
            
            if (empty($programs)) {
                continue;
            }
            
            $stats['programs_found'] += count($programs);
            
            // Insert each program
            foreach ($programs as $program) {
                $program_name = $program['program_name'] ?? '';
                $description = $program['description'] ?? null;
                $topic_category = $program['topic_category'] ?? $row['topic_category'];
                $target_audience = $program['target_audience'] ?? null;
                $age_range = $program['age_range'] ?? null;
                $duration = $program['duration'] ?? null;
                $location = $program['location'] ?? null;
                $program_type = $program['program_type'] ?? 'other';
                $language = $program['language'] ?? 'hebrew';
                
                if (empty($program_name)) {
                    continue;
                }
                
                $insert_stmt->bind_param("ssiisssssss", 
                    $program_name, $description, $url_id, $org_id, $topic_category,
                    $target_audience, $age_range, $duration, $location, $program_type, $language);
                
                if ($insert_stmt->execute()) {
                    $stats['programs_inserted']++;
                } else {
                    $stats['errors']++;
                    error_log("Error inserting program '{$program_name}': " . $insert_stmt->error);
                }
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
            
        } catch (Exception $e) {
            $stats['errors']++;
            error_log("Error processing URL {$url_id}: " . $e->getMessage());
        }
    }
    
    $insert_stmt->close();
    closeDbConnection($conn);
    
    return $stats;
}

