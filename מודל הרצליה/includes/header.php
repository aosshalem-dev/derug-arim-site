<?php
require_once(__DIR__ . '/../auth/check.php');
$currentUser = getCurrentUserEmail();
?>
<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'מיפוי קשרים' ?></title>
    <?php
    // קביעת נתיב בסיס לפי מיקום הקובץ
    $basePath = strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '../' : '';
    $pagesPath = $basePath . 'pages/';
    $authPath = $basePath . 'auth/';
    $assetsPath = $basePath . 'assets/';
    ?>
    <link rel="stylesheet" href="<?= $assetsPath ?>style.css">
    <script src="https://cdn.jsdelivr.net/npm/cytoscape@3.26.0/dist/cytoscape.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dagre@0.8.5/dist/dagre.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cytoscape-dagre@2.5.0/cytoscape-dagre.min.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">מיפוי קשרים</h1>
            <div class="nav-links">
                <a href="<?= $pagesPath ?>index.php">דף ראשי</a>
                <a href="<?= $pagesPath ?>list.php">כל המידע</a>
                <a href="<?= $pagesPath ?>add_node.php">הוסף צומת</a>
                <a href="<?= $pagesPath ?>add_edge.php">הוסף קשר</a>
                <a href="<?= $pagesPath ?>search.php">חיפוש</a>
                <span class="user-info"><?= htmlspecialchars($currentUser) ?></span>
                <a href="<?= $authPath ?>logout.php">יציאה</a>
            </div>
        </div>
    </nav>
    <main class="main-content">

