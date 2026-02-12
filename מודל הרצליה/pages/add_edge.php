<?php
$pageTitle = 'הוספת קשר חדש';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../models/Node.php');
require_once(__DIR__ . '/../models/Edge.php');
require_once(__DIR__ . '/../models/Evidence.php');

$nodeModel = new Node();
$edgeModel = new Edge();
$evidenceModel = new Evidence();
$userId = getCurrentUserId();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fromId = $_POST['from_node_id'] ?? null;
    $toId = $_POST['to_node_id'] ?? null;
    $relType = $_POST['rel_type'] ?? null;
    
    if (!$fromId || !$toId || !$relType) {
        $error = 'נא למלא את כל השדות הנדרשים';
    } else {
        $data = [
            'confidence' => $_POST['confidence'] ?? 'medium',
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null
        ];
        
        try {
            $edgeId = $edgeModel->create($fromId, $toId, $relType, $data, $userId);
            
            // הוספת ראיות
            if (!empty($_POST['evidence_source_ref'])) {
                $evidenceData = [
                    'source_type' => $_POST['evidence_source_type'],
                    'source_ref' => $_POST['evidence_source_ref'],
                    'quote_snippet' => $_POST['evidence_quote'] ?? null,
                    'page' => $_POST['evidence_page'] ?? null
                ];
                $evidenceModel->addToEdge($edgeId, $evidenceData, $userId);
            }
            
            $success = true;
            header("Location: view_node.php?id=$fromId&edge_created=1");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// טעינת כל הצמתים לבחירה
$allNodes = $nodeModel->search('');
?>

<div class="form-container">
    <h2>הוספת קשר חדש</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="from_node_id">מצומת *</label>
            <select id="from_node_id" name="from_node_id" required>
                <option value="">בחר צומת</option>
                <?php foreach ($allNodes as $n): ?>
                    <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['label']) ?> (<?= $n['type'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="rel_type">סוג קשר *</label>
            <select id="rel_type" name="rel_type" required>
                <option value="">בחר סוג קשר</option>
                <optgroup label="מימון ושותפויות">
                    <option value="FUNDED_BY">מימן על ידי</option>
                    <option value="PARTNERED_WITH">שותפות עם</option>
                </optgroup>
                <optgroup label="תעסוקה ופעילות">
                    <option value="EMPLOYED_AT">מועסק ב</option>
                    <option value="INVOLVED_IN">מעורב ב</option>
                </optgroup>
                <optgroup label="קידום רעיונות ומושגים">
                    <option value="PROMOTES">מקדם</option>
                    <option value="USES_TERM">משתמש במושג</option>
                    <option value="ADVOCATES">תומך/מקדם</option>
                    <option value="DEFINES">מגדיר</option>
                    <option value="INFLUENCED_BY">הושפע מ</option>
                </optgroup>
                <optgroup label="תוכן ומדיה">
                    <option value="QUOTED">ציטט</option>
                    <option value="CONTAINS_TERM">מכיל מושג</option>
                    <option value="REPORTS_ON">מדווח על</option>
                </optgroup>
                <optgroup label="תגובות ואירועים">
                    <option value="RESPONDED_TO">הגיב ל</option>
                    <option value="OCCURRED_AT">קרה ב</option>
                </optgroup>
            </select>
        </div>
        
        <div class="form-group">
            <label for="to_node_id">לצומת *</label>
            <select id="to_node_id" name="to_node_id" required>
                <option value="">בחר צומת</option>
                <?php foreach ($allNodes as $n): ?>
                    <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['label']) ?> (<?= $n['type'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="confidence">רמת ביטחון</label>
            <select id="confidence" name="confidence">
                <option value="high">גבוהה</option>
                <option value="medium" selected>בינונית</option>
                <option value="low">נמוכה</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="start_date">תאריך התחלה</label>
            <input type="date" id="start_date" name="start_date">
        </div>
        
        <div class="form-group">
            <label for="end_date">תאריך סיום</label>
            <input type="date" id="end_date" name="end_date">
        </div>
        
        <hr style="margin: 30px 0;">
        <h3>הוספת ראיה (חובה!)</h3>
        
        <div class="form-group">
            <label for="evidence_source_type">סוג מקור *</label>
            <select id="evidence_source_type" name="evidence_source_type" required>
                <option value="url">קישור (URL)</option>
                <option value="pdf">קובץ PDF</option>
                <option value="quote">ציטוט</option>
                <option value="image">תמונה</option>
                <option value="screenshot">צילום מסך</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="evidence_source_ref">קישור/נתיב *</label>
            <input type="text" id="evidence_source_ref" name="evidence_source_ref" required placeholder="https://... או נתיב לקובץ">
        </div>
        
        <div class="form-group">
            <label for="evidence_quote">ציטוט רלוונטי</label>
            <textarea id="evidence_quote" name="evidence_quote" placeholder="העתק כאן את הציטוט הרלוונטי"></textarea>
        </div>
        
        <div class="form-group">
            <label for="evidence_page">עמוד/שורות</label>
            <input type="text" id="evidence_page" name="evidence_page" placeholder="למשל: עמוד 5, שורות 10-15">
        </div>
        
        <button type="submit" class="btn btn-success">שמור קשר</button>
        <a href="index.php" class="btn">ביטול</a>
    </form>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>

