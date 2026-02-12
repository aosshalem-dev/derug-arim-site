-- סכמת Database למערכת דירוג ציונות
-- שלב ראשון: טבלת קישורים בסיסית
-- משתמש ב-database הקיים - לא יוצר database חדש

-- טבלת קישורים
-- מכילה את כל הקישורים הייחודיים שנאספו
-- שם הטבלה מתחיל ב-"ranking_" כפי שנדרש
CREATE TABLE IF NOT EXISTS ranking_urls (
    id INT PRIMARY KEY AUTO_INCREMENT,
    url VARCHAR(2048) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_url (url(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- הערות:
-- - url הוא UNIQUE כדי למנוע כפילויות
-- - INDEX על url(255) כדי לשפר ביצועי חיפוש
-- - utf8mb4 תומך בכל התווים העבריים והמיוחדים

-- שלב שני: הוספת עמודות למטא-דאטה בסיסי
-- הוספת עמודות לחילוץ מטא-דאטה מהקישורים
-- הערה: אם העמודות כבר קיימות, יש להריץ את migrate_add_metadata_columns.php במקום
ALTER TABLE ranking_urls 
ADD COLUMN source_type VARCHAR(100) NULL COMMENT 'סוג המקור, למשל: municipality, government, news',
ADD COLUMN year INT NULL COMMENT 'שנה שחולצה מהתוכן או מה-URL',
ADD COLUMN metadata_extracted_at TIMESTAMP NULL COMMENT 'מתי חולץ המטא-דאטה',
ADD COLUMN metadata_status ENUM('pending', 'extracted', 'failed') DEFAULT 'pending' COMMENT 'סטטוס חילוץ המטא-דאטה';

-- שלב שלישי: הוספת שדות מטא-דאטה מורחבים לניתוח אידיאולוגי ותרבותי
-- Institutional Context - הקשר מוסדי
ALTER TABLE ranking_urls 
ADD COLUMN organization_type ENUM('municipality', 'government_agency', 'media', 'educational_institution', 'ngo', 'research_institution', 'other') NULL COMMENT 'סוג הארגון המפרסם - תיאורי בלבד',
ADD COLUMN jurisdiction_level ENUM('local', 'regional', 'national', 'international') NULL COMMENT 'רמת סמכות שיפוט - מקומי, אזורי, לאומי, בינלאומי',
ADD COLUMN geographic_scope VARCHAR(200) NULL COMMENT 'היקף גיאוגרפי - שם עיר, אזור, או תחום אחריות';

-- Content Domain - תחום תוכן
ALTER TABLE ranking_urls 
ADD COLUMN topic_category ENUM('education', 'culture', 'policy', 'news', 'research', 'heritage', 'community', 'other') NULL COMMENT 'קטגוריית נושא - תיאור תחום התוכן',
ADD COLUMN document_type ENUM('report', 'article', 'policy_document', 'curriculum', 'announcement', 'protocol', 'plan', 'other') NULL COMMENT 'סוג מסמך - תיאור פורמט התוכן',
ADD COLUMN target_audience ENUM('general_public', 'educators', 'students', 'policymakers', 'researchers', 'community_leaders', 'other') NULL COMMENT 'קהל יעד - למי מיועד התוכן';

-- Ideological Indicators - אינדיקטורים אידיאולוגיים (תיאוריים בלבד)
ALTER TABLE ranking_urls 
ADD COLUMN values_orientation JSON NULL COMMENT 'אוריינטציית ערכים - מערך תיאורי של ערכים המופיעים (collective_identity, individual_rights, tradition, innovation, etc.)',
ADD COLUMN cultural_focus ENUM('hebrew_culture', 'jewish_heritage', 'israeli_identity', 'multicultural', 'universal', 'mixed', 'unclear') NULL COMMENT 'מוקד תרבותי - תיאור המוקד התרבותי של התוכן';

-- Identity and Zionism References - זהות וציונות
ALTER TABLE ranking_urls 
ADD COLUMN zionism_references ENUM('explicit', 'implicit', 'none', 'unclear') NULL COMMENT 'התייחסויות לציונות - תיאור נוכחות (לא הערכה)',
ADD COLUMN identity_theme JSON NULL COMMENT 'נושאי זהות - מערך תיאורי (national_identity, religious_identity, cultural_identity, etc.)',
ADD COLUMN historical_periods JSON NULL COMMENT 'תקופות היסטוריות - מערך תיאורי (biblical, ancient, medieval, modern, contemporary, etc.)';

-- Content Characteristics - מאפיינים טכניים
ALTER TABLE ranking_urls 
ADD COLUMN language ENUM('hebrew', 'english', 'arabic', 'mixed', 'other') NULL COMMENT 'שפת התוכן',
ADD COLUMN accessibility_level ENUM('public', 'restricted', 'unclear') NULL COMMENT 'רמת נגישות',
ADD COLUMN publication_format ENUM('pdf', 'html', 'text', 'image', 'video', 'other') NULL COMMENT 'פורמט פרסום';

-- Temporal Context - הקשר זמני
ALTER TABLE ranking_urls 
ADD COLUMN period_referenced VARCHAR(200) NULL COMMENT 'תקופה שמתייחסים אליה בתוכן',
ADD COLUMN temporal_scope ENUM('historical', 'contemporary', 'future', 'mixed', 'unclear') NULL COMMENT 'היקף זמני';

-- Quality Indicators - אינדיקטורי איכות
ALTER TABLE ranking_urls 
ADD COLUMN completeness ENUM('complete', 'partial', 'summary', 'unclear') NULL COMMENT 'שלמות התוכן',
ADD COLUMN reliability_indicators JSON NULL COMMENT 'אינדיקטורי אמינות - מערך תיאורי (official_source, peer_reviewed, verified, etc.)';

-- Additional columns added later
ALTER TABLE ranking_urls 
ADD COLUMN short_summary TEXT NULL COMMENT 'סיכום קצר שנוצר על ידי AI',
ADD COLUMN failure_reason TEXT NULL COMMENT 'סיבה לכישלון חילוץ מטא-דאטה',
ADD COLUMN relevance_level TINYINT NULL COMMENT 'רמת רלוונטיות ידנית (1-5)',
ADD COLUMN manual_summary TEXT NULL COMMENT 'סיכום ידני',
ADD COLUMN ai_relevance_score TINYINT NULL COMMENT 'דירוג רלוונטיות AI (1-5)',
ADD COLUMN ai_relevance_reason VARCHAR(255) NULL COMMENT 'סיבה לדירוג AI או למה לא דורג',
ADD COLUMN ai_relevance_model VARCHAR(64) NULL COMMENT 'מודל AI שבו השתמשו',
ADD COLUMN ai_relevance_updated_at DATETIME NULL COMMENT 'תאריך עדכון דירוג AI',
ADD COLUMN ai_relevance_status ENUM('pending','done','skipped','error') DEFAULT NULL COMMENT 'סטטוס דירוג AI',
ADD COLUMN organization_name VARCHAR(255) NULL COMMENT 'שם הגוף (למשל שם העירייה)';

-- Indexes
CREATE INDEX IF NOT EXISTS idx_relevance_level ON ranking_urls (relevance_level);
CREATE INDEX IF NOT EXISTS idx_ai_relevance_score ON ranking_urls (ai_relevance_score);
CREATE INDEX IF NOT EXISTS idx_ai_relevance_status ON ranking_urls (ai_relevance_status);
CREATE INDEX IF NOT EXISTS idx_organization_name ON ranking_urls (organization_name);
