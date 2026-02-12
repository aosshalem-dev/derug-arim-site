<?php
$pageTitle = 'דף ראשי - מיפוי קשרים';
require_once(__DIR__ . '/../includes/header.php');
?>
<div style="margin-bottom: 20px;">
    <a href="list.php" class="btn">צפה בכל המידע</a>
    <a href="add_node.php" class="btn btn-success">+ הוסף צומת</a>
    <a href="add_edge.php" class="btn btn-success">+ הוסף קשר</a>
</div>

<div class="graph-container">
    <h2>מפת הקשרים</h2>
    
    <?php
    require_once(__DIR__ . '/../models/Node.php');
    $nodeModel = new Node();
    $nodeCount = count($nodeModel->search(''));
    if ($nodeCount == 0): ?>
        <div class="alert alert-info">
            <p><strong>אין צמתים במערכת עדיין.</strong></p>
            <p>התחל ב<a href="add_node.php">הוספת צומת ראשון</a> או <a href="list.php">צפה בכל המידע</a></p>
        </div>
    <?php endif; ?>
    
    <div class="graph-controls">
        <select id="filterType">
            <option value="">כל הסוגים</option>
            <option value="org">ארגונים</option>
            <option value="person">אנשים</option>
            <option value="program">תוכניות</option>
            <option value="term">מושגים</option>
            <option value="concept">רעיונות</option>
            <option value="event">אירועים</option>
            <option value="article">כתבות</option>
        </select>
        
        <input type="text" id="searchNode" placeholder="חיפוש צומת...">
        
        <button class="btn" onclick="loadGraph()">רענן גרף</button>
        <button class="btn" onclick="resetView()">איפוס תצוגה</button>
    </div>
    
    <div id="cy"></div>
    
    <div id="nodeInfo" style="margin-top: 20px; display: none;">
        <h3>פרטי צומת</h3>
        <div id="nodeDetails"></div>
    </div>
</div>

<script>
let cy;

// טעינת הגרף
async function loadGraph(nodeId = null) {
    const url = nodeId 
        ? `../api/graph.php?node_id=${nodeId}&depth=2`
        : '../api/graph.php';
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (cy) {
            cy.destroy();
        }
        
        // הכנת צבעים לצמתים לפי flags
        const nodeStyles = data.nodes.map(node => {
            const flags = node.data.flags || [];
            let bgColor = '#666';
            if (flags.includes('problematic')) bgColor = '#d00';
            else if (flags.includes('suspicious')) bgColor = '#f80';
            
            return {
                ...node,
                style: {
                    ...node.style,
                    'background-color': bgColor
                }
            };
        });
        
        cy = cytoscape({
            container: document.getElementById('cy'),
            elements: [...nodeStyles, ...data.edges],
            style: [
                {
                    selector: 'node',
                    style: {
                        'label': 'data(label)',
                        'width': 30,
                        'height': 30,
                        'text-valign': 'bottom',
                        'text-margin-y': 5,
                        'font-size': '12px',
                        'color': '#fff',
                        'text-outline-width': 2,
                        'text-outline-color': '#000'
                    }
                },
                {
                    selector: 'edge',
                    style: {
                        'width': 2,
                        'line-color': '#999',
                        'target-arrow-color': '#999',
                        'target-arrow-shape': 'triangle',
                        'curve-style': 'bezier',
                        'label': 'data(label)',
                        'font-size': '10px'
                    }
                }
            ],
            layout: {
                name: 'breadthfirst',  // layout פשוט שלא דורש תלויות נוספות
                directed: true,
                spacingFactor: 1.5
            }
        });
        
        // אירוע לחיצה על צומת
        cy.on('tap', 'node', function(evt) {
            const nodeId = evt.target.id();
            showNodeInfo(nodeId);
        });
        
    } catch (error) {
        console.error('Error loading graph:', error);
        showAlert('שגיאה בטעינת הגרף', 'error');
    }
}

// הצגת פרטי צומת
async function showNodeInfo(nodeId) {
    try {
        const response = await fetch(`../api/nodes.php?id=${nodeId}`);
        const node = await response.json();
        
        const detailsDiv = document.getElementById('nodeDetails');
        detailsDiv.innerHTML = `
            <p><strong>שם:</strong> ${node.label}</p>
            <p><strong>סוג:</strong> ${node.type}</p>
            <p><strong>תיאור:</strong> ${node.description || 'אין תיאור'}</p>
            <p><strong>תיוגים:</strong> ${(node.flags || []).join(', ') || 'אין'}</p>
            <p><a href="view_node.php?id=${nodeId}" class="btn">צפה בפרטים מלאים</a></p>
        `;
        
        document.getElementById('nodeInfo').style.display = 'block';
    } catch (error) {
        console.error('Error loading node info:', error);
    }
}

// איפוס תצוגה
function resetView() {
    if (cy) {
        cy.fit();
        cy.center();
    }
}

// טעינה ראשונית
loadGraph();

// חיפוש
document.getElementById('searchNode').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const query = this.value;
        if (query) {
            fetch(`../api/nodes.php?search=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(nodes => {
                    if (nodes.length > 0) {
                        loadGraph(nodes[0].id);
                    } else {
                        showAlert('לא נמצאו תוצאות', 'error');
                    }
                });
        }
    }
});

// פילטר לפי סוג
document.getElementById('filterType').addEventListener('change', function() {
    const type = this.value;
    if (type) {
        fetch(`../api/graph.php?type=${type}`)
            .then(r => r.json())
            .then(data => {
                if (cy) {
                    cy.elements().remove();
                    cy.add([...data.nodes, ...data.edges]);
                }
            });
    } else {
        loadGraph();
    }
});
</script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>

