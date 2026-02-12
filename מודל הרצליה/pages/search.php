<?php
$pageTitle = 'חיפוש ומסלולים';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../models/Node.php');
require_once(__DIR__ . '/../models/Edge.php');

$nodeModel = new Node();
$edgeModel = new Edge();

$results = [];
$path = null;
$query = $_GET['q'] ?? '';
$type = $_GET['type'] ?? '';
$fromId = $_GET['from'] ?? null;
$toId = $_GET['to'] ?? null;

if ($query || $type) {
    $results = $nodeModel->search($query, $type ?: null);
}

if ($fromId && $toId) {
    $path = $edgeModel->getPath((int)$fromId, (int)$toId);
}
?>

<div class="form-container">
    <h2>חיפוש צמתים</h2>
    
    <form method="GET">
        <div class="form-group">
            <label for="q">חיפוש</label>
            <input type="text" id="q" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="הזן שם או תיאור...">
        </div>
        
        <div class="form-group">
            <label for="type">סוג</label>
            <select id="type" name="type">
                <option value="">כל הסוגים</option>
                <option value="org" <?= $type === 'org' ? 'selected' : '' ?>>ארגונים</option>
                <option value="person" <?= $type === 'person' ? 'selected' : '' ?>>אנשים</option>
                <option value="program" <?= $type === 'program' ? 'selected' : '' ?>>תוכניות</option>
                <option value="term" <?= $type === 'term' ? 'selected' : '' ?>>מושגים</option>
                <option value="concept" <?= $type === 'concept' ? 'selected' : '' ?>>רעיונות</option>
                <option value="event" <?= $type === 'event' ? 'selected' : '' ?>>אירועים</option>
            </select>
        </div>
        
        <button type="submit" class="btn">חפש</button>
    </form>
</div>

<?php if (!empty($results)): ?>
    <div class="table-container">
        <h3>תוצאות חיפוש (<?= count($results) ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>שם</th>
                    <th>סוג</th>
                    <th>תיאור</th>
                    <th>תיוגים</th>
                    <th>פעולות</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $node): ?>
                    <tr>
                        <td><?= htmlspecialchars($node['label']) ?></td>
                        <td><span class="node-type"><?= htmlspecialchars($node['type']) ?></span></td>
                        <td><?= htmlspecialchars(substr($node['description'] ?? '', 0, 100)) ?>...</td>
                        <td>
                            <?php foreach ($node['flags'] ?? [] as $flag): ?>
                                <span class="flag flag-<?= $flag ?>"><?= htmlspecialchars($flag) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <a href="view_node.php?id=<?= $node['id'] ?>" class="btn">צפה</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="form-container" style="margin-top: 40px;">
    <h2>מציאת מסלול בין צמתים</h2>
    
    <form method="GET">
        <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
        
        <div class="form-group">
            <label for="from">מצומת (ID)</label>
            <input type="number" id="from" name="from" value="<?= htmlspecialchars($fromId ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="to">לצומת (ID)</label>
            <input type="number" id="to" name="to" value="<?= htmlspecialchars($toId ?? '') ?>" required>
        </div>
        
        <button type="submit" class="btn">מצא מסלול</button>
    </form>
    
    <?php if ($path !== null): ?>
        <?php if (empty($path)): ?>
            <div class="alert alert-error" style="margin-top: 20px;">לא נמצא מסלול בין הצמתים</div>
        <?php else: ?>
            <div class="alert alert-success" style="margin-top: 20px;">
                נמצא מסלול עם <?= count($path) ?> קשרים
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>

