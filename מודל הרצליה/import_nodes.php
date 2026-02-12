<?php
/**
 * סקריפט ייבוא צמתים מקובץ JSON
 * טוען צמתים למסד הנתונים רק אם הם לא קיימים
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/auth/check.php');
require_once(__DIR__ . '/models/Node.php');
require_once(__DIR__ . '/db/connection.php');

$userId = getCurrentUserId();
$jsonFile = __DIR__ . '/data/sample_nodes.json';

echo "<!DOCTYPE html><html dir='rtl' lang='he'><head><meta charset='UTF-8'>";
echo "<title>ייבוא צמתים מ-JSON</title>";
echo "<style>
body { font-family: Arial; max-width: 900px; margin: 50px auto; padding: 20px; }
.success { color: green; padding: 5px; }
.error { color: red; padding: 5px; }
.warning { color: orange; padding: 5px; }
.info { color: blue; padding: 5px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 10px; text-align: right; border: 1px solid #ddd; }
th { background: #f0f0f0; }
</style></head><body>";

echo "<h1>ייבוא צמתים מקובץ JSON</h1>";

// בדיקה אם הקובץ קיים
if (!file_exists($jsonFile)) {
    die("<p class='error'>✗ הקובץ לא נמצא: $jsonFile</p></body></html>");
}

// קריאת הקובץ
$jsonContent = file_get_contents($jsonFile);
if ($jsonContent === false) {
    die("<p class='error'>✗ לא הצלחתי לקרוא את הקובץ</p></body></html>");
}

// פענוח JSON
$nodes = json_decode($jsonContent, true);
if ($nodes === null) {
    $error = json_last_error_msg();
    die("<p class='error'>✗ שגיאה בפענוח JSON: $error</p></body></html>");
}

if (!is_array($nodes)) {
    die("<p class='error'>✗ הקובץ צריך להכיל מערך של צמתים</p></body></html>");
}

echo "<p class='info'>✓ נמצאו " . count($nodes) . " צמתים בקובץ</p>";

// סוגי צמתים תקינים
$validTypes = ['org', 'person', 'program', 'term', 'concept', 'doc', 'funding', 'event', 'article'];

$nodeModel = new Node();
$pdo = getDB();

$stats = [
    'total' => count($nodes),
    'created' => 0,
    'skipped' => 0,
    'errors' => 0,
    'errors_list' => []
];

echo "<h2>תהליך הייבוא:</h2>";
echo "<table>";
echo "<tr><th>#</th><th>שם</th><th>סוג</th><th>מפתח ייחודי</th><th>תוצאה</th></tr>";

foreach ($nodes as $index => $nodeData) {
    $rowNum = $index + 1;
    
    // אימות בסיסי
    $errors = [];
    
    if (empty($nodeData['type'])) {
        $errors[] = "חסר type";
    } elseif (!in_array($nodeData['type'], $validTypes)) {
        $errors[] = "type לא תקין: " . $nodeData['type'];
    }
    
    if (empty($nodeData['label'])) {
        $errors[] = "חסר label";
    }
    
    if (!empty($nodeData['flags']) && !is_array($nodeData['flags'])) {
        $errors[] = "flags צריך להיות מערך";
    }
    
    if (!empty($nodeData['props']) && !is_array($nodeData['props'])) {
        $errors[] = "props צריך להיות אובייקט";
    }
    
    if (!empty($errors)) {
        $stats['errors']++;
        $stats['errors_list'][] = [
            'row' => $rowNum,
            'label' => $nodeData['label'] ?? 'לא ידוע',
            'errors' => $errors
        ];
        echo "<tr>";
        echo "<td>$rowNum</td>";
        echo "<td>" . htmlspecialchars($nodeData['label'] ?? 'לא ידוע') . "</td>";
        echo "<td>" . htmlspecialchars($nodeData['type'] ?? 'לא ידוע') . "</td>";
        echo "<td>" . htmlspecialchars($nodeData['canonical_key'] ?? 'אין') . "</td>";
        echo "<td class='error'>✗ שגיאות: " . implode(', ', $errors) . "</td>";
        echo "</tr>";
        continue;
    }
    
    // בדיקה אם הצומת כבר קיים
    $exists = false;
    $existingId = null;
    
    if (!empty($nodeData['canonical_key'])) {
        // בדיקה לפי canonical_key
        $stmt = $pdo->prepare("SELECT id FROM nodes WHERE type = ? AND canonical_key = ?");
        $stmt->execute([$nodeData['type'], $nodeData['canonical_key']]);
        $existing = $stmt->fetch();
        if ($existing) {
            $exists = true;
            $existingId = $existing['id'];
        }
    } else {
        // בדיקה לפי type + label
        $stmt = $pdo->prepare("SELECT id FROM nodes WHERE type = ? AND label = ?");
        $stmt->execute([$nodeData['type'], $nodeData['label']]);
        $existing = $stmt->fetch();
        if ($existing) {
            $exists = true;
            $existingId = $existing['id'];
        }
    }
    
    if ($exists) {
        $stats['skipped']++;
        echo "<tr>";
        echo "<td>$rowNum</td>";
        echo "<td>" . htmlspecialchars($nodeData['label']) . "</td>";
        echo "<td>" . htmlspecialchars($nodeData['type']) . "</td>";
        echo "<td>" . htmlspecialchars($nodeData['canonical_key'] ?? 'אין') . "</td>";
        echo "<td class='warning'>⚠ דילוג - הצומת כבר קיים (ID: $existingId)</td>";
        echo "</tr>";
        continue;
    }
    
    // יצירת הצומת
    try {
        $nodeId = $nodeModel->create($nodeData, $userId);
        $stats['created']++;
        echo "<tr>";
        echo "<td>$rowNum</td>";
        echo "<td>" . htmlspecialchars($nodeData['label']) . "</td>";
        echo "<td>" . htmlspecialchars($nodeData['type']) . "</td>";
        echo "<td>" . htmlspecialchars($nodeData['canonical_key'] ?? 'אין') . "</td>";
        echo "<td class='success'>✓ נוצר בהצלחה (ID: $nodeId)</td>";
        echo "</tr>";
    } catch (Exception $e) {
        $stats['errors']++;
        $stats['errors_list'][] = [
            'row' => $rowNum,
            'label' => $nodeData['label'],
            'error' => $e->getMessage()
        ];
        echo "<tr>";
        echo "<td>$rowNum</td>";
        echo "<td>" . htmlspecialchars($nodeData['label']) . "</td>";
        echo "<td>" . htmlspecialchars($nodeData['type']) . "</td>";
        echo "<td>" . htmlspecialchars($nodeData['canonical_key'] ?? 'אין') . "</td>";
        echo "<td class='error'>✗ שגיאה: " . htmlspecialchars($e->getMessage()) . "</td>";
        echo "</tr>";
    }
}

echo "</table>";

// סיכום
echo "<h2>סיכום:</h2>";
echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 8px;'>";
echo "<p><strong>סה\"כ צמתים בקובץ:</strong> {$stats['total']}</p>";
echo "<p class='success'><strong>נוצרו:</strong> {$stats['created']}</p>";
echo "<p class='warning'><strong>דולגו (כבר קיימים):</strong> {$stats['skipped']}</p>";
echo "<p class='error'><strong>שגיאות:</strong> {$stats['errors']}</p>";
echo "</div>";

if (!empty($stats['errors_list'])) {
    echo "<h3>פרטי שגיאות:</h3>";
    echo "<ul>";
    foreach ($stats['errors_list'] as $error) {
        echo "<li>";
        echo "<strong>שורה {$error['row']}</strong> - " . htmlspecialchars($error['label']) . ": ";
        if (isset($error['errors'])) {
            echo implode(', ', $error['errors']);
        } else {
            echo htmlspecialchars($error['error']);
        }
        echo "</li>";
    }
    echo "</ul>";
}

echo "<p style='margin-top: 30px;'>";
echo "<a href='pages/list.php' style='display: inline-block; padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;'>← חזור לרשימת כל הצמתים</a>";
echo "</p>";
echo "</body></html>";
?>
