<?php
/**
 * Unified API endpoint for AI relevance rating
 * Handles start, status, process, and cancel operations
 */

// Start output buffering FIRST
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Register error handler to catch all errors
set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return false;
});

// Set headers FIRST before any output
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

try {
    // relevance.php is in public/api/ai/, so we need to go up 3 levels to reach ranking-v2 root
    $db_file = __DIR__ . '/../../../src/config/database.php';
    if (!file_exists($db_file)) {
        throw new Exception("Database config file not found: $db_file");
    }
    require_once $db_file;
    
    $api_key_file = __DIR__ . '/../../../src/config/api_key.php';
    if (!file_exists($api_key_file)) {
        throw new Exception("API key file not found: $api_key_file");
    }
    require_once $api_key_file;
    
    $url_fetcher_file = __DIR__ . '/../../../src/lib/url_fetcher.php';
    if (!file_exists($url_fetcher_file)) {
        throw new Exception("URL fetcher file not found: $url_fetcher_file");
    }
    require_once $url_fetcher_file;
    
    $migrate_file = __DIR__ . '/../../../src/utils/migrate_add_ai_relevance.php';
    if (!file_exists($migrate_file)) {
        throw new Exception("Migration file not found: $migrate_file");
    }
    require_once $migrate_file;
    
    $prompt_loader_file = __DIR__ . '/../../../src/lib/prompt_loader.php';
    if (!file_exists($prompt_loader_file)) {
        throw new Exception("Prompt loader file not found: $prompt_loader_file");
    }
    require_once $prompt_loader_file;
} catch (Exception $e) {
    ob_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    http_response_code(500);
    error_log("Error loading files in relevance.php: " . $e->getMessage());
    error_log("File: " . $e->getFile() . ", Line: " . $e->getLine());
    echo json_encode([
        'success' => false,
        'error' => 'שגיאה בטעינת קבצים נדרשים: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

// PHP compatibility helper
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

function sendResponse($data) {
    // Clean any previous output
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    // Ensure headers are set
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    
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

function rateUrlWithAI($url) {
    try {
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
            CURLOPT_MAXFILESIZE => 5000000,
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
        
        if (stripos($content_type, 'application/pdf') !== false || substr($html_content, 0, 4) === '%PDF') {
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
        
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html_content, $matches)) {
            $title = strip_tags($matches[1]);
            $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $title = trim($title);
        }
        
        if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\']/is', $html_content, $matches)) {
            $meta = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $meta = trim($meta);
        }
        
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
        
        $text = strip_tags($html_content);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (empty($text) || mb_strlen($text) < 50) {
            return [
                'rating' => null,
                'confidence' => 'low',
                'reason' => 'insufficient text content',
                'status' => 'skipped'
            ];
        }
        
        if (mb_strlen($text) > 12000) {
            $text = mb_substr($text, 0, 12000) . '...';
        }
        
        $headings_text = implode(' ', array_slice($headings, 0, 10));
        if (!empty($headings_text)) {
            $text = $headings_text . ' ' . $text;
            if (mb_strlen($text) > 12000) {
                $text = mb_substr($text, 0, 12000) . '...';
            }
        }
        
        // Call OpenAI API - load prompt from template
        $prompt = loadPrompt('relevance_rating', [
            'URL' => $url,
            'TITLE' => $title ?: 'N/A',
            'META_DESCRIPTION' => $meta ?: 'N/A',
            'TEXT' => $text
        ]);
        
        $systemMessage = loadSystemMessage('relevance_rating');
        if (empty($systemMessage)) {
            $systemMessage = 'You are a strict classifier. Return ONLY valid JSON.';
        }
        
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
                    ['role' => 'system', 'content' => $systemMessage],
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
        
        $rating = isset($ai_result['rating']) ? $ai_result['rating'] : null;
        $confidence = isset($ai_result['confidence']) ? $ai_result['confidence'] : 'low';
        $reason = isset($ai_result['reason']) ? substr($ai_result['reason'], 0, 160) : '';
        
        if ($confidence !== 'high') {
            $rating = null;
            if (empty($reason)) {
                $reason = "confidence too low: $confidence";
            }
        }
        
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
    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? $_GET['action'] : null;
    
    // Handle POST requests
    if ($method === 'POST') {
        $raw_input = file_get_contents('php://input');
        if ($raw_input === false || $raw_input === '') {
            sendResponse([
                'success' => false,
                'error' => 'No input received'
            ]);
        }
        
        $input = json_decode($raw_input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendResponse([
                'success' => false,
                'error' => 'Invalid JSON input: ' . json_last_error_msg() . ' | Raw: ' . substr($raw_input, 0, 200)
            ]);
        }
        
        if (!$input) {
            $input = [];
        }
        
        $postAction = isset($input['action']) ? $input['action'] : 'start';
        
        if ($postAction === 'start_rating' || $postAction === 'start') {
            // Start rating job
            try {
                $only_unrated = isset($input['only_unrated']) ? (bool)$input['only_unrated'] : true;
                $limit = isset($input['limit']) ? (int)$input['limit'] : 0;
                
                $conn = getDbConnection();
                
                if (!$conn) {
                    throw new Exception('שגיאת חיבור למסד הנתונים');
                }
                
                ensureAiRelevanceColumns($conn);
                
                if (!$conn) {
                    throw new Exception('שגיאת חיבור למסד הנתונים');
                }
                
                $where = $only_unrated ? "WHERE (ai_relevance_score IS NULL AND (ai_relevance_status IS NULL OR ai_relevance_status = ''))" : "";
                $query = "SELECT COUNT(*) as total FROM ranking_urls $where";
                $result = $conn->query($query);
                
                if (!$result) {
                    throw new Exception('שגיאה בשאילתת DB: ' . $conn->error);
                }
                
                $row = $result->fetch_assoc();
                if (!$row) {
                    throw new Exception('שגיאה בקבלת תוצאות מהמסד נתונים');
                }
                
                $total = isset($row['total']) ? (int)$row['total'] : 0;
                
                $job_id = 'job_' . time() . '_' . uniqid();
                $job_file = sys_get_temp_dir() . '/ai_relevance_job_' . $job_id . '.json';
                
                if (!is_writable(sys_get_temp_dir())) {
                    throw new Exception('תיקיית temp לא ניתנת לכתיבה: ' . sys_get_temp_dir());
                }
                
                $job_data = [
                    'job_id' => $job_id,
                    'total' => $total,
                    'processed' => 0,
                    'done' => 0,
                    'skipped' => 0,
                    'error' => 0,
                    'current_url' => null,
                    'last_processed_id' => 0,
                    'started_at' => date('Y-m-d H:i:s'),
                    'only_unrated' => $only_unrated,
                    'limit' => $limit,
                    'cancel' => false
                ];
                
                $write_result = file_put_contents($job_file, json_encode($job_data));
                if ($write_result === false) {
                    throw new Exception('שגיאה בכתיבת קובץ עבודה ל: ' . $job_file);
                }
                
                closeDbConnection($conn);
                
                sendResponse([
                    'success' => true,
                    'job_id' => $job_id,
                    'total' => $total,
                    'message' => 'Job started'
                ]);
            } catch (Exception $e) {
                error_log("Error starting AI rating job: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                sendResponse([
                    'success' => false,
                    'error' => 'שגיאה בהתחלת עבודה: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
            
        } elseif ($postAction === 'cancel_rating' || $postAction === 'cancel') {
            // Cancel job
            $job_id = isset($input['job_id']) ? $input['job_id'] : null;
            if (!$job_id) {
                sendResponse(['success' => false, 'error' => 'job_id required']);
            }
            
            $job_data = getJobData($job_id);
            if (!$job_data) {
                sendResponse(['success' => false, 'error' => 'Job not found']);
            }
            
            $job_data['cancel'] = true;
            saveJobData($job_id, $job_data);
            
            sendResponse([
                'success' => true,
                'message' => 'Job cancelled',
                'progress' => $job_data
            ]);
        }
    }
    
    // Handle GET requests
    if ($method === 'GET') {
        $job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;
        
        if (!$job_id) {
            sendResponse(['success' => false, 'error' => 'job_id required']);
        }
        
        // Default to 'status' if action is not specified but job_id is provided
        if (!$action || $action === 'status') {
            // Get status
            $job_data = getJobData($job_id);
            if (!$job_data) {
                sendResponse(['success' => false, 'error' => 'Job not found']);
            }
            
            sendResponse([
                'success' => true,
                'progress' => $job_data,
                'completed' => $job_data['processed'] >= $job_data['total'],
                'cancelled' => $job_data['cancel'] ?? false
            ]);
            
        } elseif ($action === 'process') {
            // Process one URL
            $job_data = getJobData($job_id);
            if (!$job_data) {
                sendResponse(['success' => false, 'error' => 'Job not found']);
            }
            
            if ($job_data['cancel']) {
                sendResponse([
                    'success' => true,
                    'cancelled' => true,
                    'progress' => $job_data
                ]);
            }
            
            if ($job_data['processed'] >= $job_data['total']) {
                sendResponse([
                    'success' => true,
                    'completed' => true,
                    'progress' => $job_data
                ]);
            }
            
            try {
                $conn = getDbConnection();
                
                if (!$conn) {
                    throw new Exception('שגיאת חיבור למסד הנתונים');
                }
                
                ensureAiRelevanceColumns($conn);
                
                // Get the next unrated record
                // Use last_processed_id if available, otherwise start from beginning
                $last_id = isset($job_data['last_processed_id']) ? (int)$job_data['last_processed_id'] : 0;
                
                if ($job_data['only_unrated']) {
                    // Get next unrated record with ID greater than last processed
                    // Check both ai_relevance_score IS NULL AND (ai_relevance_status IS NULL OR ai_relevance_status = '')
                    // to avoid reprocessing records that were already processed but skipped
                    $query = "SELECT id, url FROM ranking_urls 
                              WHERE (ai_relevance_score IS NULL AND (ai_relevance_status IS NULL OR ai_relevance_status = '')) 
                              AND id > ? 
                              ORDER BY id ASC LIMIT 1";
                    $stmt = $conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception('שגיאה בהכנת שאילתה: ' . $conn->error);
                    }
                    $stmt->bind_param("i", $last_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    // Get next record with ID greater than last processed
                    $query = "SELECT id, url FROM ranking_urls 
                              WHERE id > ? 
                              ORDER BY id ASC LIMIT 1";
                    $stmt = $conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception('שגיאה בהכנת שאילתה: ' . $conn->error);
                    }
                    $stmt->bind_param("i", $last_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                }
                
                if (!$result) {
                    throw new Exception('שגיאה בשאילתת DB');
                }
                
                $row = $result->fetch_assoc();
                
                // If no row found, check if we're done
                if (!$row) {
                    // Count remaining unrated records
                    if ($job_data['only_unrated']) {
                        $count_query = "SELECT COUNT(*) as count FROM ranking_urls 
                                        WHERE (ai_relevance_score IS NULL AND (ai_relevance_status IS NULL OR ai_relevance_status = ''))";
                    } else {
                        $count_query = "SELECT COUNT(*) as count FROM ranking_urls";
                    }
                    $count_result = $conn->query($count_query);
                    if ($count_result) {
                        $count_row = $count_result->fetch_assoc();
                        $remaining = (int)$count_row['count'];
                        
                        if ($remaining === 0) {
                            $job_data['processed'] = $job_data['total'];
                            saveJobData($job_id, $job_data);
                            if (isset($stmt)) $stmt->close();
                            closeDbConnection($conn);
                            sendResponse([
                                'success' => true,
                                'completed' => true,
                                'progress' => $job_data
                            ]);
                        }
                    }
                    
                    // No more records to process
                    $job_data['processed'] = $job_data['total'];
                    saveJobData($job_id, $job_data);
                    if (isset($stmt)) $stmt->close();
                    closeDbConnection($conn);
                    sendResponse([
                        'success' => true,
                        'completed' => true,
                        'progress' => $job_data
                    ]);
                }
                
                if ($row) {
                    if (isset($stmt)) $stmt->close();
                $job_data['current_url'] = $row['url'];
                saveJobData($job_id, $job_data);
                
                $rating_result = rateUrlWithAI($row['url']);
                
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
                
                $job_data['processed']++;
                $job_data['last_processed_id'] = $row['id']; // Store last processed ID
                if ($status === 'done') {
                    $job_data['done']++;
                } elseif ($status === 'skipped') {
                    $job_data['skipped']++;
                } elseif ($status === 'error') {
                    $job_data['error']++;
                }
                $job_data['current_url'] = null;
                
                saveJobData($job_id, $job_data);
                
                usleep(500000); // 0.5 seconds
                
                closeDbConnection($conn);
                
                sendResponse([
                    'success' => true,
                    'progress' => $job_data,
                    'last_url' => $row['url'],
                    'last_result' => $rating_result,
                    'completed' => $job_data['processed'] >= $job_data['total']
                ]);
            } else {
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
                error_log("Error processing URL: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                if (isset($conn)) {
                    closeDbConnection($conn);
                }
                sendResponse([
                    'success' => false,
                    'error' => 'שגיאה בעיבוד URL: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        }
    }
    
    sendResponse(['success' => false, 'error' => 'Invalid request']);
    
} catch (Exception $e) {
    error_log("Error in AI relevance API: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Ensure we can send response even if there was an error
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'שגיאה: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
    
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    
    exit;
}


