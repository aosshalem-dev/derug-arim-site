<?php
/**
 * קובץ התקנה ראשונית
 * להריץ פעם אחת כדי ליצור את כל הטבלאות
 */

echo "<!DOCTYPE html><html dir='rtl' lang='he'><head><meta charset='UTF-8'><title>התקנה ראשונית</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{color:green;}.error{color:red;}</style></head><body>";
echo "<h1>התקנה ראשונית - מערכת מיפוי קשרים</h1>";

require_once(__DIR__ . '/db/schema.php');

echo "</body></html>";
?>

