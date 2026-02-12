<?php
/**
 * API endpoint for creating or updating a record
 * Enhanced with comprehensive error handling
 */

// Start output buffering FIRST - before anything else
ob_start();

// Error handling setup
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Helper function to send error response
function sendErrorResponse($httpCode, $error, $details = null) {
    ob_clean();
    http_response_code($httpCode);
    $response = [
        'success' => false,
        'error' => $error
    ];
    if ($details !== null) {
        $response['details'] = $details;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

// Helper function to send success response
function sendSuccessResponse($message, $record) {
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => $message,
        'record' => $record
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
    exit;
}

// Helper function to log errors
function logError($message, $context = []) {
    $logMessage = "[save_record.php] " . $message;
    if (!empty($context)) {
        $logMessage .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    error_log($logMessage);
}

// Main try-catch wrapper
try {
    // Set headers
    header('Content-Type: application/json; charset=utf-8');
    
    // Step 1: Load required files with error handling
    try {
        require_once '../config/database.php';
        logError("✓ Database config loaded");
    } catch (Exception $e) {
        logError("✗ Failed to load database config", ['error' => $e->getMessage()]);
        sendErrorResponse(500, 'שגיאה בטעינת הגדרות מסד הנתונים', $e->getMessage());
    }
    
    try {
        require_once '../ensure_metadata_columns.php';
        logError("✓ Metadata columns file loaded");
    } catch (Exception $e) {
        logError("✗ Failed to load metadata columns file", ['error' => $e->getMessage()]);
        sendErrorResponse(500, 'שגיאה בטעינת קובץ מטא-דאטה', $e->getMessage());
    }
    
    // Step 2: Ensure metadata columns with error handling
    try {
        $metadataResult = ensureMetadataColumns();
        if (isset($metadataResult['success']) && !$metadataResult['success']) {
            logError("✗ Metadata columns check failed", ['result' => $metadataResult]);
            sendErrorResponse(500, 'שגיאה בבדיקת עמודות מטא-דאטה', $metadataResult);
        }
        logError("✓ Metadata columns ensured");
    } catch (Exception $e) {
        logError("✗ Exception in ensureMetadataColumns", ['error' => $e->getMessage()]);
        sendErrorResponse(500, 'שגיאה בבדיקת עמודות מטא-דאטה', $e->getMessage());
    }
    
    // Step 3: Get JSON input
    $rawInput = file_get_contents('php://input');
    logError("Raw input received", ['length' => strlen($rawInput)]);
    
    $input = json_decode($rawInput, true);
    if (!$input) {
        $jsonError = json_last_error_msg();
        logError("✗ Invalid JSON input", ['error' => $jsonError, 'input_preview' => substr($rawInput, 0, 200)]);
        sendErrorResponse(400, 'נתונים לא תקינים', ['json_error' => $jsonError]);
    }
    
    logError("✓ JSON input parsed successfully", ['has_id' => isset($input['id']), 'has_url' => isset($input['url'])]);
    
    // Step 4: Validate required fields
    if (empty($input['url'])) {
        logError("✗ Missing URL field");
        sendErrorResponse(400, 'URL הוא שדה חובה');
    }
    
    // Step 5: Connect to database with error handling
    try {
        $conn = getDbConnection();
        logError("✓ Database connection established");
    } catch (Exception $e) {
        logError("✗ Database connection failed", ['error' => $e->getMessage()]);
        sendErrorResponse(500, 'שגיאת חיבור למסד הנתונים', $e->getMessage());
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : null;
    $isUpdate = $id !== null;
    
    logError("Processing " . ($isUpdate ? "UPDATE" : "INSERT"), ['id' => $id]);
    
    // Step 6: Prepare JSON fields
    try {
        $valuesOrientation = !empty($input['values_orientation']) ? json_encode($input['values_orientation'], JSON_UNESCAPED_UNICODE) : null;
        $identityTheme = !empty($input['identity_theme']) ? json_encode($input['identity_theme'], JSON_UNESCAPED_UNICODE) : null;
        $historicalPeriods = !empty($input['historical_periods']) ? json_encode($input['historical_periods'], JSON_UNESCAPED_UNICODE) : null;
        $reliabilityIndicators = !empty($input['reliability_indicators']) ? json_encode($input['reliability_indicators'], JSON_UNESCAPED_UNICODE) : null;
        logError("✓ JSON fields prepared");
    } catch (Exception $e) {
        logError("✗ Failed to prepare JSON fields", ['error' => $e->getMessage()]);
        closeDbConnection($conn);
        sendErrorResponse(500, 'שגיאה בהכנת שדות JSON', $e->getMessage());
    }
    
    // Step 7: Prepare query and bind parameters
    $shortSummary = isset($input['short_summary']) && !empty($input['short_summary']) ? trim($input['short_summary']) : null;
    $manualSummary = isset($input['manual_summary']) && !empty($input['manual_summary']) ? trim($input['manual_summary']) : null;
    
    // Handle relevance_level: accept 1-5, convert empty string or 0 to null
    $relevanceLevel = null;
    if (isset($input['relevance_level']) && $input['relevance_level'] !== '' && $input['relevance_level'] !== null) {
        $relevanceLevelInt = (int)$input['relevance_level'];
        if ($relevanceLevelInt >= 1 && $relevanceLevelInt <= 5) {
            $relevanceLevel = $relevanceLevelInt;
        }
    }
    
    // Handle ai_relevance_score: accept 1-5, convert empty string or 0 to null
    $aiRelevanceScore = null;
    if (isset($input['ai_relevance_score']) && $input['ai_relevance_score'] !== '' && $input['ai_relevance_score'] !== null) {
        $aiRelevanceScoreInt = (int)$input['ai_relevance_score'];
        if ($aiRelevanceScoreInt >= 1 && $aiRelevanceScoreInt <= 5) {
            $aiRelevanceScore = $aiRelevanceScoreInt;
        }
    }
    
    // Handle ai_relevance_reason
    $aiRelevanceReason = isset($input['ai_relevance_reason']) && !empty($input['ai_relevance_reason']) ? trim($input['ai_relevance_reason']) : null;
    
    if ($isUpdate) {
        // Update existing record
        $query = "UPDATE ranking_urls SET
            url = ?,
            source_type = ?,
            year = ?,
            organization_name = ?,
            organization_type = ?,
            jurisdiction_level = ?,
            geographic_scope = ?,
            topic_category = ?,
            document_type = ?,
            target_audience = ?,
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
            short_summary = ?,
            relevance_level = ?,
            manual_summary = ?,
            ai_relevance_score = ?,
            ai_relevance_reason = ?,
            metadata_status = ?
        WHERE id = ?";
        
        try {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                logError("✗ Failed to prepare UPDATE query", ['error' => $conn->error]);
                closeDbConnection($conn);
                sendErrorResponse(500, 'שגיאה בהכנת שאילתת עדכון', $conn->error);
            }
            
            // Prepare variables for bind_param (must be variables, not direct values)
            $url = $input['url'];
            $sourceType = $input['source_type'] ?? null;
            $year = isset($input['year']) && $input['year'] !== '' ? (int)$input['year'] : null;
            $organizationName = isset($input['organization_name']) && !empty($input['organization_name']) ? trim($input['organization_name']) : null;
            $organizationType = $input['organization_type'] ?? null;
            $jurisdictionLevel = $input['jurisdiction_level'] ?? null;
            $geographicScope = $input['geographic_scope'] ?? null;
            $topicCategory = $input['topic_category'] ?? null;
            $documentType = $input['document_type'] ?? null;
            $targetAudience = $input['target_audience'] ?? null;
            $culturalFocus = $input['cultural_focus'] ?? null;
            $zionismReferences = $input['zionism_references'] ?? null;
            $language = $input['language'] ?? null;
            $accessibilityLevel = $input['accessibility_level'] ?? null;
            $publicationFormat = $input['publication_format'] ?? null;
            $periodReferenced = $input['period_referenced'] ?? null;
            $temporalScope = $input['temporal_scope'] ?? null;
            $completeness = $input['completeness'] ?? null;
            $metadataStatus = $input['metadata_status'] ?? 'pending';
            
            // Type string: s=string, i=integer
            // Parameters: url(s), source_type(s), year(i), organization_name(s), organization_type(s), jurisdiction_level(s), 
            // geographic_scope(s), topic_category(s), document_type(s), target_audience(s), 
            // values_orientation(s), cultural_focus(s), zionism_references(s), identity_theme(s), 
            // historical_periods(s), language(s), accessibility_level(s), publication_format(s), 
            // period_referenced(s), temporal_scope(s), completeness(s), reliability_indicators(s), 
            // short_summary(s), relevance_level(i), manual_summary(s), ai_relevance_score(i), ai_relevance_reason(s), metadata_status(s), id(i)
            // Total: 29 parameters
            $stmt->bind_param("ssisssssssssssssssssssissisi",
                $url,
                $sourceType,
                $year,
                $organizationName,
                $organizationType,
                $jurisdictionLevel,
                $geographicScope,
                $topicCategory,
                $documentType,
                $targetAudience,
                $valuesOrientation,
                $culturalFocus,
                $zionismReferences,
                $identityTheme,
                $historicalPeriods,
                $language,
                $accessibilityLevel,
                $publicationFormat,
                $periodReferenced,
                $temporalScope,
                $completeness,
                $reliabilityIndicators,
                $shortSummary,
                $relevanceLevel,
                $manualSummary,
                $aiRelevanceScore,
                $aiRelevanceReason,
                $metadataStatus,
                $id
            );
            
            logError("✓ UPDATE query prepared and bound");
        } catch (Exception $e) {
            logError("✗ Exception preparing UPDATE query", ['error' => $e->getMessage()]);
            closeDbConnection($conn);
            sendErrorResponse(500, 'שגיאה בהכנת שאילתת עדכון', $e->getMessage());
        }
    } else {
        // Insert new record
        $query = "INSERT INTO ranking_urls (
            url, source_type, year, organization_name, organization_type, jurisdiction_level,
            geographic_scope, topic_category, document_type, target_audience,
            values_orientation, cultural_focus, zionism_references,
            identity_theme, historical_periods, language, accessibility_level,
            publication_format, period_referenced, temporal_scope,
            completeness, reliability_indicators, short_summary, relevance_level, manual_summary, ai_relevance_score, ai_relevance_reason, metadata_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                logError("✗ Failed to prepare INSERT query", ['error' => $conn->error]);
                closeDbConnection($conn);
                sendErrorResponse(500, 'שגיאה בהכנת שאילתת הוספה', $conn->error);
            }
            
            // Prepare variables for bind_param (must be variables, not direct values)
            $url = $input['url'];
            $sourceType = $input['source_type'] ?? null;
            $year = isset($input['year']) && $input['year'] !== '' ? (int)$input['year'] : null;
            $organizationType = $input['organization_type'] ?? null;
            $jurisdictionLevel = $input['jurisdiction_level'] ?? null;
            $geographicScope = $input['geographic_scope'] ?? null;
            $topicCategory = $input['topic_category'] ?? null;
            $documentType = $input['document_type'] ?? null;
            $targetAudience = $input['target_audience'] ?? null;
            $culturalFocus = $input['cultural_focus'] ?? null;
            $zionismReferences = $input['zionism_references'] ?? null;
            $language = $input['language'] ?? null;
            $accessibilityLevel = $input['accessibility_level'] ?? null;
            $publicationFormat = $input['publication_format'] ?? null;
            $periodReferenced = $input['period_referenced'] ?? null;
            $temporalScope = $input['temporal_scope'] ?? null;
            $completeness = $input['completeness'] ?? null;
            $metadataStatus = $input['metadata_status'] ?? 'pending';
            
            $stmt->bind_param("ssisssssssssssssssssssssissisi",
                $url,
                $sourceType,
                $year,
                $organizationName,
                $organizationType,
                $jurisdictionLevel,
                $geographicScope,
                $topicCategory,
                $documentType,
                $targetAudience,
                $valuesOrientation,
                $culturalFocus,
                $zionismReferences,
                $identityTheme,
                $historicalPeriods,
                $language,
                $accessibilityLevel,
                $publicationFormat,
                $periodReferenced,
                $temporalScope,
                $completeness,
                $reliabilityIndicators,
                $shortSummary,
                $relevanceLevel,
                $manualSummary,
                $aiRelevanceScore,
                $aiRelevanceReason,
                $metadataStatus
            );
            
            logError("✓ INSERT query prepared and bound");
        } catch (Exception $e) {
            logError("✗ Exception preparing INSERT query", ['error' => $e->getMessage()]);
            closeDbConnection($conn);
            sendErrorResponse(500, 'שגיאה בהכנת שאילתת הוספה', $e->getMessage());
        }
    }
    
    // Step 8: Execute query
    try {
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $errno = $stmt->errno;
            $stmt->close();
            closeDbConnection($conn);
            
            logError("✗ Query execution failed", [
                'error' => $error,
                'errno' => $errno,
                'query_type' => $isUpdate ? 'UPDATE' : 'INSERT',
                'relevance_level_received' => isset($input['relevance_level']) ? $input['relevance_level'] : 'not set',
                'relevance_level_processed' => $relevanceLevel
            ]);
            
            sendErrorResponse(500, 'שגיאה בשמירת הרשומה', [
                'message' => $error,
                'errno' => $errno,
                'query_type' => $isUpdate ? 'UPDATE' : 'INSERT',
                'relevance_level_received' => isset($input['relevance_level']) ? $input['relevance_level'] : 'not set',
                'relevance_level_processed' => $relevanceLevel
            ]);
        }
        
        logError("✓ Query executed successfully");
    } catch (Exception $e) {
        $stmt->close();
        closeDbConnection($conn);
        logError("✗ Exception executing query", ['error' => $e->getMessage()]);
        sendErrorResponse(500, 'שגיאה בביצוע שאילתה', $e->getMessage());
    }
    
    $recordId = $isUpdate ? $id : $conn->insert_id;
    logError("Record ID determined", ['record_id' => $recordId, 'is_update' => $isUpdate]);
    
    // Step 9: Fetch the saved record
    $fetchQuery = "SELECT * FROM ranking_urls WHERE id = ?";
    try {
        $fetchStmt = $conn->prepare($fetchQuery);
        if (!$fetchStmt) {
            $stmt->close();
            closeDbConnection($conn);
            logError("✗ Failed to prepare fetch query", ['error' => $conn->error]);
            sendErrorResponse(500, 'שגיאה בהכנת שאילתת שליפה', $conn->error);
        }
        
        $fetchStmt->bind_param("i", $recordId);
        if (!$fetchStmt->execute()) {
            $stmt->close();
            $fetchStmt->close();
            closeDbConnection($conn);
            logError("✗ Failed to execute fetch query", ['error' => $fetchStmt->error]);
            sendErrorResponse(500, 'שגיאה בשליפת הרשומה', $fetchStmt->error);
        }
        
        $result = $fetchStmt->get_result();
        $record = $result->fetch_assoc();
        $fetchStmt->close();
        
        if (!$record) {
            $stmt->close();
            closeDbConnection($conn);
            logError("✗ Record not found after save", ['record_id' => $recordId]);
            sendErrorResponse(500, 'הרשומה לא נמצאה לאחר השמירה');
        }
        
        logError("✓ Record fetched successfully", ['record_id' => $recordId]);
    } catch (Exception $e) {
        $stmt->close();
        if (isset($fetchStmt)) {
            $fetchStmt->close();
        }
        closeDbConnection($conn);
        logError("✗ Exception fetching record", ['error' => $e->getMessage()]);
        sendErrorResponse(500, 'שגיאה בשליפת הרשומה', $e->getMessage());
    }
    
    // Step 10: Cleanup and send success response
    $stmt->close();
    closeDbConnection($conn);
    
    logError("✓ Save operation completed successfully", ['record_id' => $recordId]);
    
    sendSuccessResponse(
        $isUpdate ? 'רשומה עודכנה בהצלחה' : 'רשומה נוצרה בהצלחה',
        [
            'id' => (int)$record['id'],
            'url' => $record['url']
        ]
    );
    
} catch (Exception $e) {
    // Catch any unhandled exceptions
    logError("✗ Unhandled exception", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Cleanup if connection exists
    if (isset($conn)) {
        try {
            closeDbConnection($conn);
        } catch (Exception $cleanupError) {
            logError("✗ Error during cleanup", ['error' => $cleanupError->getMessage()]);
        }
    }
    
    sendErrorResponse(500, 'שגיאה לא צפויה', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    // Catch PHP 7+ Errors (TypeError, ParseError, etc.)
    logError("✗ Fatal error", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Cleanup if connection exists
    if (isset($conn)) {
        try {
            closeDbConnection($conn);
        } catch (Exception $cleanupError) {
            logError("✗ Error during cleanup", ['error' => $cleanupError->getMessage()]);
        }
    }
    
    sendErrorResponse(500, 'שגיאה קריטית', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
