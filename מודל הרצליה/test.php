<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>בדיקת PHP</h1>";
echo "<p>PHP עובד!</p>";
echo "<p>גרסת PHP: " . phpversion() . "</p>";

echo "<h2>בדיקת קבצים:</h2>";
$files = [
    'config.php',
    'db/connection.php',
    'auth/login.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file קיים</p>";
    } else {
        echo "<p style='color: red;'>✗ $file לא קיים</p>";
    }
}

echo "<h2>בדיקת config:</h2>";
if (file_exists('config.php')) {
    require_once('config.php');
    if (isset($db_config)) {
        echo "<p style='color: green;'>✓ config.php נטען בהצלחה</p>";
        echo "<pre>";
        print_r($db_config);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ $db_config לא מוגדר</p>";
    }
}

echo "<h2>בדיקת DB:</h2>";
try {
    require_once('db/connection.php');
    $pdo = getDB();
    echo "<p style='color: green;'>✓ חיבור ל-DB הצליח!</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>מספר משתמשים: " . $result['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ שגיאה: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

