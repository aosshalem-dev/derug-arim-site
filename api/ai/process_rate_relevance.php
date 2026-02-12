<?php
/**
 * Worker script to process AI relevance rating
 * This processes URLs one by one and updates job status
 * Should be called repeatedly until job is complete
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../../config/database.php';
require_once '../../config/api_key.php';
require_once '../../lib/url_fetcher.php';
require_once '../../database/migrate_add_ai_relevance.php';

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

function rateUrlWithAI($url, $conn) {
    try {
        // Fetch page content using cURL to get raw HTML
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_MAXFILESIZE => 5000000, // 5MB limit
        ]);
        
        $html_content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            return [
                'rating' => null,
                'confidence' => 'low',
                'reason' => 'fetch failed: ' . substr($curl_error, 0, 100),
                'status' => 'skipped'
            ];
        }
        
        if ($http_code !== 200) {
            return [
                'rating' => null,
                'confidence' => 'low',
                'reason' => "HTTP $http_code",
                'status' => 'skipped'
            ];
        }
        
        // Check if PDF or binary
        if (stripos($content_type, 'application/pdf') !== false || 
            substr($html_content, 0, 4) === '%PDF') {
            return [
                'rating' => null,
                'confidence' => 'low',
                'reason' => 'PDF file - not accessible',
                'status' => 'skipped'
            ];
        }
        
        // Extract title and meta
        $title = '';
        $meta = '';
        $text = '';
        
        // Try to extract title from HTML
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html_content, $matches)) {
            $title = strip_tags($matches[1]);
            $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $title = trim($title);
        }
        
        // Try to extract meta description
        if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\']/is', $html_content, $matches)) {
            $meta = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $meta = trim($meta);
        }
        
        // Extract headings (h1-h3)
        $headings = [];
        if (preg_match_all('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/is', $html_content, $heading_matches)) {
            foreach ($heading_matches[1] as $heading) {
                $heading = strip_tags($heading);
                $heading = html_entity_decode($heading, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $heading = trim($heading);
                if (!empty($heading)) {
                    $headings[] = $heading;
                }
            }
        }
        
        // Extract text content (limit to 12k chars)
        $text = strip_tags($html_content);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Check if content is accessible and text-heavy
        if (empty($text) || mb_strlen($text) < 50) {
            return [
                'rating' => null,
                'confidence' => 'low',
                'reason' => 'insufficient text content',
                'status' => 'skipped'
            ];
        }
        
        // Limit text to 12k chars
        if (mb_strlen($text) > 12000) {
            $text = mb_substr($text, 0, 12000) . '...';
        }
        
        // Combine headings into text if available
        $headings_text = implode(' ', array_slice($headings, 0, 10));
        if (!empty($headings_text)) {
            $text = $headings_text . ' ' . $text;
            if (mb_strlen($text) > 12000) {
                $text = mb_substr($text, 0, 12000) . '...';
            }
        }
        
        // Call OpenAI API
        $prompt = "We have a web page. Determine ideological relevance in municipal education/culture context.\n" .
                  "You MUST NOT guess. If insufficient evidence → rating=null.\n\n" .
                  "Page URL: $url\n" .
                  "Title: " . ($title ?: 'N/A') . "\n" .
                  "Meta description: " . ($meta ?: 'N/A') . "\n" .
                  "Extracted text (may be partial): $text\n\n" .
                  "Return JSON ONLY with this schema:\n" .
                  "{\n" .
                  '  "rating": 1|2|3|4|5|null,' . "\n" .
                  '  "confidence": "high"|"medium"|"low",' . "\n" .
                  '  "signals": [ "short bullet strings, max 6" ],' . "\n" .
                  '  "reason": "max 160 chars"' . "\n" .
                  "}\n\n" .
                  "Rules:\n" .
                  "- rating is 5 only if strong explicit identity-related content OR multiple strong keyword/theme signals.\n" .
                  "- rating is 4 if clear program/policy framing around the keyword cluster but less explicit identity.\n" .
                  "- rating is 3 if mild/indirect relevance.\n" .
                  "- rating is 1–2 if clearly unrelated (only if confident).\n" .
                  "- If confidence is medium/low → set rating=null.\n\n" .
                  "High score triggers:\n" .
                  "A) Directly related to identity / Zionism / Jewish identity / national identity / ערכים לאומיים / ציונות / יהדות / \"זהות\" / \"שייכות\" / \"מורשת\"\n" .
                  "B) Strong signals via keywords: \"סובלנות\", \"הכלה\", \"מגוון\", \"מוגנות\", \"שוויון מגדרי\", \"להטב\", \"אקלים\", \"קיימות\", \"צדק חברתי\", \"שוויון\", \"אקטיביזם\", \"SEL/למידה רגשית-חברתית\", \"דמוקרטיה\", \"דו-קיום\", \"אזרחות גלובלית\", \"חוסן קהילתי\", \"מניעת גזענות\", \"הטיות\", \"פריבילגיה\", \"הדרה\"\n\n" .
                  "Low score: Clearly unrelated (e.g., plumbing tender, road work, pure sports results) → rating 1–2 only if confident.";
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENAI_API_KEY
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a strict classifier. Return ONLY valid JSON.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
                'max_tokens' => 500,
                'response_format' => ['type' => 'json_object']
            ]),
            CURLOPT_TIMEOUT => 60
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            throw new Exception("cURL error: $curl_error");
        }
        
        if ($http_code !== 200) {
            $error_data = json_decode($response, true);
            $error_msg = isset($error_data['error']['message']) ? $error_data['error']['message'] : "HTTP $http_code";
            throw new Exception("API error: $error_msg");
        }
        
        $response_data = json_decode($response, true);
        if (!isset($response_data['choices'][0]['message']['content'])) {
            throw new Exception("Invalid API response");
        }
        
        $ai_result = json_decode($response_data['choices'][0]['message']['content'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse AI response: " . json_last_error_msg());
        }
        
        // Validate response
        $rating = isset($ai_result['rating']) ? $ai_result['rating'] : null;
        $confidence = isset($ai_result['confidence']) ? $ai_result['confidence'] : 'low';
        $reason = isset($ai_result['reason']) ? substr($ai_result['reason'], 0, 160) : '';
        
        // Enforce confidence rule: only high confidence gets a rating
        if ($confidence !== 'high') {
            $rating = null;
            if (empty($reason)) {
                $reason = "confidence too low: $confidence";
            }
        }
        
        // Validate rating is 1-5 or null
        if ($rating !== null && ($rating < 1 || $rating > 5)) {
            $rating = null;
            $reason = "invalid rating value: $rating";
        }
        
        return [
            'rating' => $rating !== null ? (int)$rating : null,
            'confidence' => $confidence,
            'signals' => isset($ai_result['signals']) ? $ai_result['signals'] : [],
            'reason' => $reason,
            'status' => $rating !== null ? 'done' : 'skipped'
        ];
        
    } catch (Exception $e) {
        return [
            'rating' => null,
            'confidence' => 'low',
            'reason' => substr($e->getMessage(), 0, 160),
            'status' => 'error'
        ];
    }
}

try {
    $job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;
    if (!$job_id) {
        sendResponse(['success' => false, 'error' => 'job_id required']);
    }
    
    // Load job data
    $job_data = getJobData($job_id);
    if (!$job_data) {
        sendResponse(['success' => false, 'error' => 'Job not found']);
    }
    
    // Check if cancelled
    if ($job_data['cancel']) {
        sendResponse([
            'success' => true,
            'cancelled' => true,
            'progress' => $job_data
        ]);
    }
    
    // Check if done
    if ($job_data['processed'] >= $job_data['total']) {
        sendResponse([
            'success' => true,
            'completed' => true,
            'progress' => $job_data
        ]);
    }
    
    // Process one URL
    ensureAiRelevanceColumns();
    $conn = getDbConnection();
    
    $where = $job_data['only_unrated'] ? "WHERE ai_relevance_score IS NULL" : "";
    $limit_clause = $job_data['limit'] > 0 ? "LIMIT 1 OFFSET " . $job_data['processed'] : "LIMIT 1 OFFSET " . $job_data['processed'];
    
    $query = "SELECT id, url FROM ranking_urls $where ORDER BY id $limit_clause";
    $result = $conn->query($query);
    
    if ($row = $result->fetch_assoc()) {
        $job_data['current_url'] = $row['url'];
        saveJobData($job_id, $job_data);
        
        // Rate the URL
        $rating_result = rateUrlWithAI($row['url'], $conn);
        
        // Update database
        $rating = $rating_result['rating'];
        $reason = $rating_result['reason'];
        $status = $rating_result['status'];
        $model = 'gpt-4o';
        
        $update_query = "UPDATE ranking_urls SET 
            ai_relevance_score = ?,
            ai_relevance_reason = ?,
            ai_relevance_model = ?,
            ai_relevance_status = ?,
            ai_relevance_updated_at = NOW()
        WHERE id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("isssi", $rating, $reason, $model, $status, $row['id']);
        $stmt->execute();
        $stmt->close();
        
        // Update job progress
        $job_data['processed']++;
        if ($status === 'done') {
            $job_data['done']++;
        } elseif ($status === 'skipped') {
            $job_data['skipped']++;
        } elseif ($status === 'error') {
            $job_data['error']++;
        }
        $job_data['current_url'] = null;
        
        saveJobData($job_id, $job_data);
        
        // Small delay to respect rate limits
        usleep(500000); // 0.5 seconds
        
        closeDbConnection($conn);
        
        sendResponse([
            'success' => true,
            'progress' => $job_data,
            'last_url' => $row['url'],
            'last_result' => $rating_result
        ]);
    } else {
        // No more URLs
        $job_data['current_url'] = null;
        saveJobData($job_id, $job_data);
        closeDbConnection($conn);
        
        sendResponse([
            'success' => true,
            'completed' => true,
            'progress' => $job_data
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error in process_rate_relevance: " . $e->getMessage());
    sendResponse([
        'success' => false,
        'error' => 'שגיאה בעיבוד: ' . $e->getMessage()
    ]);
}

