    </main>
    <footer class="footer">
        <p>&copy; 2024 מיפוי קשרים - מערכת ניהול ידע</p>
    </footer>
    <?php
    $basePath = strpos($_SERVER['PHP_SELF'], '/pages/') !== false ? '../' : '';
    $assetsPath = $basePath . 'assets/';
    ?>
    <script src="<?= $assetsPath ?>main.js"></script>
</body>
</html>

