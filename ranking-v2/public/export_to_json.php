<?php
/**
 * סקריפט לייצוא נתוני ranking_urls לקובץ JSON
 * 
 * הסקריפט מייצא את כל הנתונים עם כל שדות המטא-דאטה
 * לקובץ JSON שניתן להוריד ולשמש לתכנון frontend
 * 
 * שימוש:
 * - דרך שורת פקודה: php export_to_json.php [--limit=N] [--status=STATUS]
 * - דרך דפדפן: פתח את הקובץ בדפדפן
 * - כפונקציה: exportRankingUrlsToJson($limit, $status_filter, $output_file)
 */

// טעינת הגדרות
require_once __DIR__ . '/../src/config/database.php';

/**
 * פונקציה לייצוא נתונים ל-JSON
 */
function exportRankingUrlsToJson($limit = null, $status_filter = null, $output_file = 'JSON/ranking_urls_export.json') {

// יצירת חיבור למסד הנתונים
$conn = getDbConnection();

// בניית שאילתה
    $query = "SELECT 
    id,
    url,
    created_at,
    source_type,
    year,
    organization_type,
    jurisdiction_level,
    geographic_scope,
    topic_category,
    document_type,
    target_audience,
    content_type,
    values_orientation,
    cultural_focus,
    zionism_references,
    identity_theme,
    historical_periods,
    language,
    accessibility_level,
    publication_format,
    period_referenced,
    temporal_scope,
    completeness,
    reliability_indicators,
    metadata_extracted_at,
    metadata_status
FROM ranking_urls";

$conditions = [];
$params = [];
$types = "";

if ($status_filter !== null) {
    $conditions[] = "metadata_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY id";

if ($limit !== null) {
    $query .= " LIMIT ?";
    $params[] = $limit;
    $types .= "i";
}

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return [
            'success' => false,
            'error' => "שגיאה בהכנת statement: " . $conn->error
        ];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [
    'export_info' => [
        'exported_at' => date('Y-m-d H:i:s'),
        'total_records' => 0,
        'filters' => [
            'status' => $status_filter ?? 'all',
            'limit' => $limit ?? 'all'
        ]
    ],
    'schema' => [
        'basic' => [
            'id' => 'INT - מזהה ייחודי',
            'url' => 'VARCHAR(2048) - כתובת הקישור',
            'created_at' => 'TIMESTAMP - תאריך יצירה'
        ],
        'institutional_context' => [
            'source_type' => 'VARCHAR(100) - סוג המקור',
            'organization_type' => 'ENUM - סוג הארגון',
            'jurisdiction_level' => 'ENUM - רמת סמכות שיפוט',
            'geographic_scope' => 'VARCHAR(200) - היקף גיאוגרפי'
        ],
        'content_domain' => [
            'topic_category' => 'ENUM - קטגוריית נושא',
            'document_type' => 'ENUM - סוג מסמך',
            'target_audience' => 'ENUM - קהל יעד',
            'content_type' => 'VARCHAR(200) - סוג תוכן ספציפי (משותף בין רשומות דומות)'
        ],
        'ideological_indicators' => [
            'values_orientation' => 'JSON - מערך ערכים',
            'cultural_focus' => 'ENUM - מוקד תרבותי'
        ],
        'identity_zionism' => [
            'zionism_references' => 'ENUM - התייחסויות לציונות',
            'identity_theme' => 'JSON - נושאי זהות',
            'historical_periods' => 'JSON - תקופות היסטוריות'
        ],
        'transparency_language' => [
            'language' => 'ENUM - שפת התוכן',
            'accessibility_level' => 'ENUM - רמת נגישות',
            'publication_format' => 'ENUM - פורמט פרסום'
        ],
        'temporal' => [
            'year' => 'INT - שנה',
            'period_referenced' => 'VARCHAR(100) - תקופה מוזכרת',
            'temporal_scope' => 'ENUM - היקף זמני'
        ],
        'source_quality' => [
            'completeness' => 'ENUM - שלמות',
            'reliability_indicators' => 'JSON - אינדיקטורי אמינות'
        ],
        'metadata' => [
            'metadata_extracted_at' => 'TIMESTAMP - מתי חולץ',
            'metadata_status' => 'ENUM - סטטוס חילוץ'
        ]
    ],
    'records' => []
];

    // עיבוד כל רשומה
    while ($row = $result->fetch_assoc()) {
    $record = [];
    
    // שדות בסיסיים
    $record['id'] = (int)$row['id'];
    $record['url'] = $row['url'];
    $record['created_at'] = $row['created_at'];
    
    // הקשר מוסדי
    $record['institutional_context'] = [
        'source_type' => $row['source_type'],
        'organization_type' => $row['organization_type'],
        'jurisdiction_level' => $row['jurisdiction_level'],
        'geographic_scope' => $row['geographic_scope']
    ];
    
    // תחום תוכן
    $record['content_domain'] = [
        'topic_category' => $row['topic_category'],
        'document_type' => $row['document_type'],
        'target_audience' => $row['target_audience'],
        'content_type' => $row['content_type']
    ];
    
    // אינדיקטורים אידיאולוגיים
    $record['ideological_indicators'] = [
        'values_orientation' => $row['values_orientation'] ? json_decode($row['values_orientation'], true) : null,
        'cultural_focus' => $row['cultural_focus']
    ];
    
    // זהות וציונות
    $record['identity_zionism'] = [
        'zionism_references' => $row['zionism_references'],
        'identity_theme' => $row['identity_theme'] ? json_decode($row['identity_theme'], true) : null,
        'historical_periods' => $row['historical_periods'] ? json_decode($row['historical_periods'], true) : null
    ];
    
    // שקיפות ושפה
    $record['transparency_language'] = [
        'language' => $row['language'],
        'accessibility_level' => $row['accessibility_level'],
        'publication_format' => $row['publication_format']
    ];
    
    // סמנים זמניים
    $record['temporal'] = [
        'year' => $row['year'] ? (int)$row['year'] : null,
        'period_referenced' => $row['period_referenced'],
        'temporal_scope' => $row['temporal_scope']
    ];
    
    // איכות מקור
    $record['source_quality'] = [
        'completeness' => $row['completeness'],
        'reliability_indicators' => $row['reliability_indicators'] ? json_decode($row['reliability_indicators'], true) : null
    ];
    
    // מטא-דאטה
    $record['metadata'] = [
        'metadata_extracted_at' => $row['metadata_extracted_at'],
        'metadata_status' => $row['metadata_status']
    ];
    
        $data['records'][] = $record;
    }

    $data['export_info']['total_records'] = count($data['records']);

    // סגירת statements
    $stmt->close();
    closeDbConnection($conn);

    // יצירת תיקייה אם לא קיימת
    $output_dir = dirname($output_file);
    if (!is_dir($output_dir) && $output_dir !== '.') {
        mkdir($output_dir, 0755, true);
    }

    // כתיבת קובץ JSON
    $json_output = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (file_put_contents($output_file, $json_output)) {
        $file_size = filesize($output_file);
        $file_size_kb = round($file_size / 1024, 2);
        
        return [
            'success' => true,
            'file' => $output_file,
            'size_kb' => $file_size_kb,
            'records' => count($data['records']),
            'exported_at' => $data['export_info']['exported_at']
        ];
    } else {
        return [
            'success' => false,
            'error' => "שגיאה בכתיבת הקובץ: $output_file"
        ];
    }
}

// אם הסקריפט רץ ישירות (לא כ-include)
if (basename($_SERVER['PHP_SELF'] ?? '') === 'export_to_json.php' || php_sapi_name() === 'cli') {
    // פרמטרים מהשורת פקודה
    $options = getopt("", ["limit:", "status:", "output:"]);
    $limit = isset($options['limit']) ? (int)$options['limit'] : null;
    $status_filter = isset($options['status']) ? $options['status'] : null;
    $output_file = isset($options['output']) ? $options['output'] : 'JSON/ranking_urls_export.json';
    
    $result = exportRankingUrlsToJson($limit, $status_filter, $output_file);
    
    if ($result['success']) {
        echo "=" . str_repeat("=", 60) . "\n";
        echo "ייצוא JSON הושלם בהצלחה!\n";
        echo "=" . str_repeat("=", 60) . "\n\n";
        echo "קובץ: " . $result['file'] . "\n";
        echo "גודל: " . $result['size_kb'] . " KB\n";
        echo "מספר רשומות: " . $result['records'] . "\n";
        echo "תאריך ייצוא: " . $result['exported_at'] . "\n\n";
        
        // אם זה דרך דפדפן, הצג קישור להורדה
        if (php_sapi_name() !== 'cli') {
            $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($result['file']));
            echo "קישור להורדה: <a href='$relative_path' download>הורד קובץ JSON</a>\n";
        } else {
            echo "הקובץ מוכן להורדה מהנתיב: " . realpath($result['file']) . "\n";
        }
        echo "\n" . str_repeat("=", 60) . "\n";
    } else {
        die("✗ " . $result['error'] . "\n");
    }
}




