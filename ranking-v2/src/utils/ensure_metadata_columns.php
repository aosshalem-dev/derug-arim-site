<?php
/**
 * סקריפט לוידוא שכל עמודות המטא-דאטה קיימות
 * 
 * הסקריפט בודק ומוסיף עמודות חסרות אוטומטית
 */

require_once __DIR__ . '/../config/database.php';

function ensureMetadataColumns() {
    $conn = getDbConnection();
    
    // רשימת כל העמודות הנדרשות
    $required_columns = [
        // Basic metadata
        'source_type' => "VARCHAR(100) NULL COMMENT 'סוג המקור, למשל: municipality, government, news'",
        'year' => "INT NULL COMMENT 'שנה שחולצה מהתוכן או מה-URL'",
        'metadata_extracted_at' => "TIMESTAMP NULL COMMENT 'מתי חולץ המטא-דאטה'",
        'metadata_status' => "ENUM('pending', 'extracted', 'failed') DEFAULT 'pending' COMMENT 'סטטוס חילוץ המטא-דאטה'",
        
        // Institutional Context
        'organization_name' => "VARCHAR(255) NULL COMMENT 'שם הגוף (למשל שם העירייה)'",
        'organization_type' => "ENUM('municipality', 'government_agency', 'media', 'educational_institution', 'ngo', 'research_institution', 'other') NULL COMMENT 'סוג הארגון המפרסם - תיאורי בלבד'",
        'jurisdiction_level' => "ENUM('local', 'regional', 'national', 'international') NULL COMMENT 'רמת סמכות שיפוט - מקומי, אזורי, לאומי, בינלאומי'",
        'geographic_scope' => "VARCHAR(200) NULL COMMENT 'היקף גיאוגרפי - שם עיר, אזור, או תחום אחריות'",
        
        // Content Domain
        'topic_category' => "ENUM('education', 'culture', 'policy', 'news', 'research', 'heritage', 'community', 'other') NULL COMMENT 'קטגוריית נושא - תיאור תחום התוכן'",
        'document_type' => "ENUM('report', 'article', 'policy_document', 'curriculum', 'announcement', 'protocol', 'plan', 'other') NULL COMMENT 'סוג מסמך - תיאור פורמט התוכן'",
        'target_audience' => "ENUM('general_public', 'educators', 'students', 'policymakers', 'researchers', 'community_leaders', 'other') NULL COMMENT 'קהל יעד - למי מיועד התוכן'",
        'content_type' => "VARCHAR(200) NULL COMMENT 'סוג תוכן - קטגוריה ספציפית (משותף בין רשומות דומות)'",
        
        // Ideological Indicators
        'values_orientation' => "JSON NULL COMMENT 'אוריינטציית ערכים - מערך תיאורי של ערכים המופיעים'",
        'cultural_focus' => "ENUM('hebrew_culture', 'jewish_heritage', 'israeli_identity', 'multicultural', 'universal', 'mixed', 'unclear') NULL COMMENT 'מוקד תרבותי - תיאור המוקד התרבותי של התוכן'",
        
        // Identity and Zionism References
        'zionism_references' => "ENUM('explicit', 'implicit', 'none', 'unclear') NULL COMMENT 'התייחסויות לציונות - תיאור נוכחות (לא הערכה)'",
        'identity_theme' => "JSON NULL COMMENT 'נושאי זהות - מערך תיאורי'",
        'historical_periods' => "JSON NULL COMMENT 'תקופות היסטוריות - מערך תקופות המוזכרות בתוכן'",
        
        // Transparency and Language
        'language' => "ENUM('hebrew', 'english', 'arabic', 'mixed', 'other') NULL COMMENT 'שפת התוכן - תיאור בלבד'",
        'accessibility_level' => "ENUM('public', 'restricted', 'unclear') NULL COMMENT 'רמת נגישות - האם התוכן ציבורי או מוגבל'",
        'publication_format' => "ENUM('pdf', 'html', 'text', 'image', 'video', 'other') NULL COMMENT 'פורמט פרסום - סוג הקובץ/מדיום'",
        
        // Temporal/Historical Markers
        'period_referenced' => "VARCHAR(100) NULL COMMENT 'תקופה מוזכרת - תיאור תקופה היסטורית המוזכרת'",
        'temporal_scope' => "ENUM('current', 'historical', 'future', 'mixed') NULL COMMENT 'היקף זמני - האם התוכן עוסק בהווה, עבר, עתיד או מעורב'",
        
        // Source Quality
        'completeness' => "ENUM('full_document', 'excerpt', 'summary', 'unclear') NULL COMMENT 'שלמות - האם זה מסמך מלא, קטע או סיכום'",
        'reliability_indicators' => "JSON NULL COMMENT 'אינדיקטורי אמינות - מערך תיאורי'",
        
        // Summary and Failure Tracking
        'short_summary' => "TEXT NULL COMMENT 'סיכום קצר בעברית של התוכן'",
        'failure_reason' => "TEXT NULL COMMENT 'סיבת כשלון בחילוץ מטא-דאטה'",
        
        // Relevance and Manual Summary
        'relevance_level' => "TINYINT NULL COMMENT 'רמת רלוונטיות בטווח 1-5'",
        'manual_summary' => "TEXT NULL COMMENT 'סיכום ידני של המשתמש'",
        
        // AI Relevance Rating
        'ai_relevance_score' => "TINYINT NULL COMMENT 'דירוג רלוונטיות AI (1-5)'",
        'ai_relevance_reason' => "VARCHAR(255) NULL COMMENT 'סיבה לדירוג או למה לא דורג'",
        'ai_relevance_model' => "VARCHAR(64) NULL COMMENT 'מודל AI שביצע את הדירוג'",
        'ai_relevance_updated_at' => "DATETIME NULL COMMENT 'מתי בוצע הדירוג'",
        'ai_relevance_status' => "ENUM('pending', 'done', 'skipped', 'error') DEFAULT NULL COMMENT 'סטטוס דירוג AI'",
    ];
    
    // בדיקת עמודות קיימות
    $existing_columns = [];
    $result = $conn->query("SHOW COLUMNS FROM ranking_urls");
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    $columns_to_add = [];
    foreach ($required_columns as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            $columns_to_add[$column] = $definition;
        }
    }
    
    if (empty($columns_to_add)) {
        closeDbConnection($conn);
        return ['success' => true, 'message' => 'כל העמודות כבר קיימות'];
    }
    
    // הוספת עמודות חסרות
    $alter_statements = [];
    foreach ($columns_to_add as $column => $definition) {
        $alter_statements[] = "ADD COLUMN $column $definition";
    }
    
    // חלוקה לקבוצות של 5 עמודות בכל פעם
    $chunks = array_chunk($alter_statements, 5);
    
    foreach ($chunks as $index => $chunk) {
        $sql = "ALTER TABLE ranking_urls " . implode(", ", $chunk);
        
        if (!$conn->query($sql)) {
            $error = $conn->error;
            closeDbConnection($conn);
            return [
                'success' => false,
                'error' => "שגיאה בהוספת קבוצה " . ($index + 1) . ": " . $error
            ];
        }
    }
    
    // הוספת אינדקסים
    $indexes_to_check = [
        'idx_source_type' => 'source_type',
        'idx_year' => 'year',
        'idx_metadata_status' => 'metadata_status',
        'idx_organization_type' => 'organization_type',
        'idx_jurisdiction_level' => 'jurisdiction_level',
        'idx_topic_category' => 'topic_category',
        'idx_document_type' => 'document_type',
        'idx_cultural_focus' => 'cultural_focus',
        'idx_zionism_references' => 'zionism_references',
        'idx_language' => 'language',
        'idx_temporal_scope' => 'temporal_scope',
        'idx_relevance_level' => 'relevance_level',
        'idx_ai_relevance_score' => 'ai_relevance_score',
        'idx_ai_relevance_status' => 'ai_relevance_status',
        'idx_organization_name' => 'organization_name',
    ];
    
    $result = $conn->query("SHOW INDEXES FROM ranking_urls");
    $existing_indexes = [];
    while ($row = $result->fetch_assoc()) {
        $existing_indexes[] = $row['Key_name'];
    }
    
    // עדכון רשימת העמודות הקיימות אחרי הוספת העמודות החדשות
    $all_existing_columns = array_merge($existing_columns, array_keys($columns_to_add));
    
    $indexes_to_add = [];
    foreach ($indexes_to_check as $index_name => $column_name) {
        if (!in_array($index_name, $existing_indexes) && in_array($column_name, $all_existing_columns)) {
            $indexes_to_add[] = "ADD INDEX $index_name ($column_name)";
        }
    }
    
    if (!empty($indexes_to_add)) {
        $sql = "ALTER TABLE ranking_urls " . implode(", ", $indexes_to_add);
        $conn->query($sql); // לא נכשל אם יש שגיאה באינדקסים
    }
    
    // עדכון כל הרשומות הקיימות לסטטוס 'pending' אם העמודה metadata_status נוספה
    if (in_array('metadata_status', array_keys($columns_to_add))) {
        $conn->query("UPDATE ranking_urls SET metadata_status = 'pending' WHERE metadata_status IS NULL");
    }
    
    closeDbConnection($conn);
    
    return [
        'success' => true,
        'message' => 'נוספו ' . count($columns_to_add) . ' עמודות חדשות',
        'columns_added' => array_keys($columns_to_add)
    ];
}





