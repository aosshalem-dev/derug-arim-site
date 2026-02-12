<?php
$pageTitle = 'רשימת כל המידע';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../models/Node.php');
require_once(__DIR__ . '/../models/Edge.php');
require_once(__DIR__ . '/../models/Evidence.php');

$nodeModel = new Node();
$edgeModel = new Edge();
$evidenceModel = new Evidence();

// קבלת כל הצמתים
$allNodes = $nodeModel->search('');

// קבלת כל הקשרים
$pdo = getDB();
$stmt = $pdo->query("
    SELECT e.*, 
           n1.label as from_label, n1.type as from_type,
           n2.label as to_label, n2.type as to_type
    FROM edges e
    JOIN nodes n1 ON e.from_node_id = n1.id
    JOIN nodes n2 ON e.to_node_id = n2.id
    ORDER BY e.created_at DESC
");
$allEdges = $stmt->fetchAll();

// סטטיסטיקה
$stats = [
    'total_nodes' => count($allNodes),
    'nodes_by_type' => [],
    'total_edges' => count($allEdges),
    'problematic_nodes' => 0
];

foreach ($allNodes as $node) {
    $type = $node['type'];
    $stats['nodes_by_type'][$type] = ($stats['nodes_by_type'][$type] ?? 0) + 1;
    
    if (in_array('problematic', $node['flags'] ?? [])) {
        $stats['problematic_nodes']++;
    }
}
?>

<div class="table-container">
    <h2>סטטיסטיקה</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: #e3f2fd; padding: 20px; border-radius: 8px;">
            <h3 style="margin: 0; color: #1976d2;"><?= $stats['total_nodes'] ?></h3>
            <p style="margin: 5px 0 0 0;">סה"כ צמתים</p>
        </div>
        <div style="background: #fff3e0; padding: 20px; border-radius: 8px;">
            <h3 style="margin: 0; color: #f57c00;"><?= $stats['total_edges'] ?></h3>
            <p style="margin: 5px 0 0 0;">סה"כ קשרים</p>
        </div>
        <div style="background: #ffebee; padding: 20px; border-radius: 8px;">
            <h3 style="margin: 0; color: #c62828;"><?= $stats['problematic_nodes'] ?></h3>
            <p style="margin: 5px 0 0 0;">צמתים בעייתיים</p>
        </div>
    </div>
    
    <h3>פילוח לפי סוגים</h3>
    <table>
        <thead>
            <tr>
                <th>סוג</th>
                <th>כמות</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats['nodes_by_type'] as $type => $count): ?>
                <tr>
                    <td><?= htmlspecialchars($type) ?></td>
                    <td><?= $count ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="table-container" style="margin-top: 30px;">
    <h2>כל הצמתים (<?= count($allNodes) ?>)</h2>
    
    <div style="margin-bottom: 20px;">
        <a href="add_node.php" class="btn btn-success">+ הוסף צומת חדש</a>
    </div>
    
    <?php if (empty($allNodes)): ?>
        <p>אין צמתים במערכת. <a href="add_node.php">הוסף צומת ראשון</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>שם</th>
                    <th>סוג</th>
                    <th>תיאור</th>
                    <th>תיוגים</th>
                    <th>נוצר</th>
                    <th>פעולות</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allNodes as $node): ?>
                    <tr>
                        <td><?= $node['id'] ?></td>
                        <td><strong><?= htmlspecialchars($node['label']) ?></strong></td>
                        <td><span class="node-type"><?= htmlspecialchars($node['type']) ?></span></td>
                        <td><?= htmlspecialchars(substr($node['description'] ?? '', 0, 100)) ?><?= strlen($node['description'] ?? '') > 100 ? '...' : '' ?></td>
                        <td>
                            <?php foreach ($node['flags'] ?? [] as $flag): ?>
                                <span class="flag flag-<?= $flag ?>"><?= htmlspecialchars($flag) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($node['created_at'])) ?></td>
                        <td>
                            <a href="view_node.php?id=<?= $node['id'] ?>" class="btn">צפה</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="table-container" style="margin-top: 30px;">
    <h2>כל הקשרים (<?= count($allEdges) ?>)</h2>
    
    <div style="margin-bottom: 20px;">
        <a href="add_edge.php" class="btn btn-success">+ הוסף קשר חדש</a>
    </div>
    
    <?php if (empty($allEdges)): ?>
        <p>אין קשרים במערכת. <a href="add_edge.php">הוסף קשר ראשון</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>מצומת</th>
                    <th>סוג קשר</th>
                    <th>לצומת</th>
                    <th>ביטחון</th>
                    <th>תאריך</th>
                    <th>פעולות</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allEdges as $edge): ?>
                    <tr>
                        <td>
                            <a href="view_node.php?id=<?= $edge['from_node_id'] ?>">
                                <?= htmlspecialchars($edge['from_label']) ?> (<?= $edge['from_type'] ?>)
                            </a>
                        </td>
                        <td><?= htmlspecialchars($edge['rel_type']) ?></td>
                        <td>
                            <a href="view_node.php?id=<?= $edge['to_node_id'] ?>">
                                <?= htmlspecialchars($edge['to_label']) ?> (<?= $edge['to_type'] ?>)
                            </a>
                        </td>
                        <td><?= htmlspecialchars($edge['confidence']) ?></td>
                        <td><?= date('d/m/Y', strtotime($edge['created_at'])) ?></td>
                        <td>
                            <a href="view_node.php?id=<?= $edge['from_node_id'] ?>">צפה</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>

