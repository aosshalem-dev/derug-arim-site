<?php
/**
 * הגדרות חיבור למסד הנתונים
 * משתמש ב-config.php אם קיים, אחרת משתמש בהגדרות ברירת מחדל
 */

// Don't set headers here - let the API endpoint handle it

// ניסיון לטעון config.php אם קיים
$config_paths = [
    __DIR__ . '/../../config.php',
    __DIR__ . '/../../../config.php',
    dirname(dirname(dirname(__DIR__))) . '/config.php'
];

$config_loaded = false;
foreach ($config_paths as $config_path) {
    if (file_exists($config_path)) {
        require_once $config_path;
        $config_loaded = true;
        break;
    }
}

if ($config_loaded && isset($db_config)) {
    // שימוש בהגדרות מ-config.php
    define('DB_HOST', $db_config['host2'] ?? 'localhost');
    define('DB_USER', $db_config['user2'] ?? 'root');
    define('DB_PASS', $db_config['pass2'] ?? '');
    define('DB_NAME', $db_config['dbname2'] ?? 'zionism_ranking');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // הגדרות ברירת מחדל (אם config.php לא קיים)
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_NAME')) define('DB_NAME', 'zionism_ranking');
    if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
}

/**
 * יצירת חיבור למסד הנתונים
 * 
 * @return mysqli|false אובייקט חיבור או false במקרה של שגיאה
 */
function getDbConnection() {
    // יצירת חיבור
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // בדיקת שגיאות חיבור
    if ($conn->connect_error) {
        throw new Exception("שגיאת חיבור למסד הנתונים: " . $conn->connect_error);
    }
    
    // הגדרת קידוד
    $conn->set_charset(DB_CHARSET);
    
    return $conn;
}

/**
 * סגירת חיבור למסד הנתונים
 * 
 * @param mysqli $conn חיבור למסד הנתונים
 */
function closeDbConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}


