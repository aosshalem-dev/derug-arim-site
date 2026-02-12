<?php
$pageTitle = 'הוספת צומת חדש';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../models/Node.php');

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $node = new Node();
    $userId = getCurrentUserId();
    
    $data = [
        'type' => $_POST['type'],
        'label' => $_POST['label'],
        'description' => $_POST['description'] ?? null,
        'canonical_key' => $_POST['canonical_key'] ?? null
    ];
    
    // flags
    $flags = [];
    if (isset($_POST['flag_problematic'])) $flags[] = 'problematic';
    if (isset($_POST['flag_suspicious'])) $flags[] = 'suspicious';
    if (isset($_POST['flag_key_player'])) $flags[] = 'key_player';
    if (isset($_POST['flag_academic'])) $flags[] = 'academic';
    if (isset($_POST['flag_ideology'])) $flags[] = 'ideology';
    if (!empty($flags)) {
        $data['flags'] = $flags;
    }
    
    // props
    $props = [];
    if (!empty($_POST['notes'])) $props['notes'] = $_POST['notes'];
    if (!empty($_POST['warnings'])) $props['warnings'] = $_POST['warnings'];
    if (!empty($props)) {
        $data['props'] = $props;
    }
    
    try {
        $nodeId = $node->create($data, $userId);
        $success = true;
        header("Location: view_node.php?id=$nodeId&created=1");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="form-container">
    <h2>הוספת צומת חדש</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="type">סוג צומת *</label>
            <select id="type" name="type" required>
                <option value="">בחר סוג</option>
                <option value="org">ארגון</option>
                <option value="person">אדם</option>
                <option value="program">תוכנית/פרויקט</option>
                <option value="term">מושג/מונח</option>
                <option value="concept">רעיון/אידאולוגיה</option>
                <option value="doc">מסמך</option>
                <option value="funding">תקציב/מימון</option>
                <option value="event">אירוע/מקרה</option>
                <option value="article">כתבה/דיווח</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="label">שם/כותרת *</label>
            <input type="text" id="label" name="label" required>
        </div>
        
        <div class="form-group">
            <label for="description">תיאור</label>
            <textarea id="description" name="description"></textarea>
        </div>
        
        <div class="form-group">
            <label for="canonical_key">מפתח ייחודי (למניעת כפילויות)</label>
            <input type="text" id="canonical_key" name="canonical_key" placeholder="למשל: org_handasiv">
        </div>
        
        <div class="form-group">
            <label>תיוגים (Flags):</label>
            <div>
                <label><input type="checkbox" name="flag_problematic"> בעייתי</label><br>
                <label><input type="checkbox" name="flag_suspicious"> מחשיד</label><br>
                <label><input type="checkbox" name="flag_key_player"> שחקן מפתח</label><br>
                <label><input type="checkbox" name="flag_academic"> אקדמי</label><br>
                <label><input type="checkbox" name="flag_ideology"> אידאולוגי</label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="notes">הערות נוספות</label>
            <textarea id="notes" name="notes"></textarea>
        </div>
        
        <div class="form-group">
            <label for="warnings">אזהרות/הערות חשובות</label>
            <textarea id="warnings" name="warnings"></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">שמור צומת</button>
        <a href="index.php" class="btn">ביטול</a>
    </form>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>

