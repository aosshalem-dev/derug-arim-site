<?php
$pageTitle = 'פרטי צומת';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../models/Node.php');
require_once(__DIR__ . '/../models/Edge.php');
require_once(__DIR__ . '/../models/Evidence.php');
require_once(__DIR__ . '/../db/connection.php');

$nodeModel = new Node();
$edgeModel = new Edge();
$evidenceModel = new Evidence();
$pdo = getDB();

$nodeId = $_GET['id'] ?? null;
if (!$nodeId) {
    header('Location: index.php');
    exit;
}

$node = $nodeModel->getById($nodeId);
if (!$node) {
    header('Location: index.php');
    exit;
}

$edges = $edgeModel->getByNode($nodeId);
$versions = $nodeModel->getVersions($nodeId);
?>

<div class="table-container">
    <h2><?= htmlspecialchars($node['label']) ?></h2>
    
    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success">צומת נוצר בהצלחה!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['edge_created'])): ?>
        <div class="alert alert-success">קשר נוצר בהצלחה!</div>
    <?php endif; ?>
    
    <div style="margin: 20px 0; background: #f9f9f9; padding: 20px; border-radius: 8px;">
        <h3>פרטים כלליים</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px; width: 150px; font-weight: bold; vertical-align: top;">ID:</td>
                <td style="padding: 10px;"><?= $node['id'] ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold; vertical-align: top;">סוג:</td>
                <td style="padding: 10px;"><span class="node-type"><?= htmlspecialchars($node['type']) ?></span></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold; vertical-align: top;">שם/כותרת:</td>
                <td style="padding: 10px;"><strong><?= htmlspecialchars($node['label']) ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold; vertical-align: top;">תיאור:</td>
                <td style="padding: 10px;"><?= nl2br(htmlspecialchars($node['description'] ?? 'אין תיאור')) ?></td>
            </tr>
            
            <?php if (!empty($node['canonical_key'])): ?>
            <tr>
                <td style="padding: 10px; font-weight: bold; vertical-align: top;">מפתח ייחודי:</td>
                <td style="padding: 10px;"><code><?= htmlspecialchars($node['canonical_key']) ?></code></td>
            </tr>
            <?php endif; ?>
            
            <?php if (!empty($node['flags'])): ?>
            <tr>
                <td style="padding: 10px; font-weight: bold; vertical-align: top;">תיוגים:</td>
                <td style="padding: 10px;">
                    <?php foreach ($node['flags'] as $flag): ?>
                        <span class="flag flag-<?= $flag ?>"><?= htmlspecialchars($flag) ?></span>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <?php if (!empty($node['props'])): ?>
            <tr>
                <td style="padding: 10px; font-weight: bold; vertical-align: top;">מאפיינים נוספים:</td>
                <td style="padding: 10px;">
                    <?php if (isset($node['props']['notes'])): ?>
                        <div style="margin-bottom: 10px;">
                            <strong>הערות:</strong><br>
                            <div style="background: #fff; padding: 10px; border-radius: 4px; margin-top: 5px;">
                                <?= nl2br(htmlspecialchars($node['props']['notes'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($node['props']['warnings'])): ?>
                        <div style="margin-bottom: 10px;">
                            <strong style="color: #c00;">אזהרות/הערות חשובות:</strong><br>
                            <div style="background: #ffe6e6; padding: 10px; border-radius: 4px; margin-top: 5px; border-left: 4px solid #c00;">
                                <?= nl2br(htmlspecialchars($node['props']['warnings'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($node['props']['tags'])): ?>
                        <div style="margin-bottom: 10px;">
                            <strong>תגיות:</strong><br>
                            <?php 
                            $tags = is_array($node['props']['tags']) ? $node['props']['tags'] : explode(',', $node['props']['tags']);
                            foreach ($tags as $tag): ?>
                                <span style="display: inline-block; background: #e0e0e0; padding: 3px 8px; border-radius: 3px; margin: 2px; font-size: 0.9em;">
                                    <?= htmlspecialchars(trim($tag)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    // הצגת כל המאפיינים הנוספים
                    $otherProps = [];
                    foreach ($node['props'] as $key => $value) {
                        if (!in_array($key, ['notes', 'warnings', 'tags'])) {
                            $otherProps[$key] = $value;
                        }
                    }
                    if (!empty($otherProps)): ?>
                        <div style="margin-top: 10px;">
                            <strong>מידע נוסף:</strong><br>
                            <div style="background: #fff; padding: 10px; border-radius: 4px; margin-top: 5px;">
                                <pre style="margin: 0; font-size: 0.9em; white-space: pre-wrap;"><?= htmlspecialchars(json_encode($otherProps, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            
            <tr>
                <td style="padding: 10px; font-weight: bold; vertical-align: top;">נוצר:</td>
                <td style="padding: 10px;">
                    <?= date('d/m/Y H:i', strtotime($node['created_at'])) ?>
                    <?php 
                    if ($node['created_by']): 
                        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                        $stmt->execute([$node['created_by']]);
                        $creator = $stmt->fetch();
                        if ($creator):
                    ?>
                        על ידי: <?= htmlspecialchars($creator['email']) ?>
                    <?php 
                        endif;
                    endif; 
                    ?>
                </td>
            </tr>
            
            <?php if ($node['updated_at'] != $node['created_at']): ?>
            <tr>
                <td style="padding: 10px; font-weight: bold; vertical-align: top;">עודכן:</td>
                <td style="padding: 10px;">
                    <?= date('d/m/Y H:i', strtotime($node['updated_at'])) ?>
                    <?php 
                    if ($node['updated_by']): 
                        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                        $stmt->execute([$node['updated_by']]);
                        $updater = $stmt->fetch();
                        if ($updater):
                    ?>
                        על ידי: <?= htmlspecialchars($updater['email']) ?>
                    <?php 
                        endif;
                    endif; 
                    ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        
        <p style="margin-top: 20px;">
            <a href="add_edge.php?from=<?= $nodeId ?>" class="btn btn-success">הוסף קשר חדש</a>
            <a href="add_node.php?edit=<?= $nodeId ?>" class="btn">ערוך צומת</a>
            <button onclick="deleteNode(<?= $nodeId ?>, '<?= htmlspecialchars($node['label'], ENT_QUOTES) ?>')" class="btn btn-danger">מחק צומת</button>
        </p>
    </div>
    
    <hr>
    
    <h3>קשרים (<?= count($edges) ?>)</h3>
    
    <?php if (empty($edges)): ?>
        <p>אין קשרים לצומת זה</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>מצומת</th>
                    <th>סוג קשר</th>
                    <th>לצומת</th>
                    <th>ביטחון</th>
                    <th>תאריכים</th>
                    <th>פעולות</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($edges as $edge): ?>
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
                        <td>
                            <?php if ($edge['start_date']): ?>
                                <?= htmlspecialchars($edge['start_date']) ?>
                                <?php if ($edge['end_date']): ?>
                                    - <?= htmlspecialchars($edge['end_date']) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view_edge.php?id=<?= $edge['id'] ?>">צפה</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <?php if (!empty($versions)): ?>
        <hr>
        <h3>גרסאות קודמות (<?= count($versions) ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>גרסה</th>
                    <th>שונה על ידי</th>
                    <th>תאריך</th>
                    <th>פעולה</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($versions as $version): ?>
                    <tr>
                        <td>#<?= $version['version_number'] ?></td>
                        <td><?= htmlspecialchars($version['changed_by_email'] ?? 'לא ידוע') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($version['changed_at'])) ?></td>
                        <td>
                            <a href="view_version.php?node_id=<?= $nodeId ?>&version=<?= $version['version_number'] ?>">צפה</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
async function deleteNode(nodeId, nodeLabel) {
    // אישור לפני מחיקה
    const confirmed = confirm('האם אתה בטוח שברצונך למחוק את הצומת "' + nodeLabel + '"?\n\nפעולה זו תמחק גם את כל הקשרים הקשורים לצומת זה.\nפעולה זו לא ניתנת לביטול!');
    
    if (!confirmed) {
        return;
    }
    
    // אישור נוסף
    const doubleConfirm = confirm('זהו אישור אחרון. האם אתה בטוח?');
    if (!doubleConfirm) {
        return;
    }
    
    try {
        const response = await fetch('../api/nodes.php?id=' + nodeId, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('הצומת נמחק בהצלחה');
            window.location.href = 'list.php';
        } else {
            alert('שגיאה במחיקת הצומת: ' + (result.error || 'שגיאה לא ידועה'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('שגיאה במחיקת הצומת');
    }
}
</script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>

