<?php
/**
 * Test script for AI relevance API
 * This will help diagnose the HTTP 500 error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Testing AI Relevance API Setup</h1>";

echo "<h2>1. Checking file paths...</h2>";
$base_dir = __DIR__;
echo "Base directory: $base_dir<br>";

$files_to_check = [
    'database.php' => __DIR__ . '/../../../src/config/database.php',
    'api_key.php' => __DIR__ . '/../../../src/config/api_key.php',
    'url_fetcher.php' => __DIR__ . '/../../../src/lib/url_fetcher.php',
    'migrate_add_ai_relevance.php' => __DIR__ . '/../../../src/utils/migrate_add_ai_relevance.php',
    'prompt_loader.php' => __DIR__ . '/../../../src/lib/prompt_loader.php',
];

foreach ($files_to_check as $name => $path) {
    $exists = file_exists($path);
    echo "$name: " . ($exists ? "✓ Found" : "✗ NOT FOUND") . " at $path<br>";
    if (!$exists) {
        echo "&nbsp;&nbsp;Absolute path: " . realpath($path) . "<br>";
    }
}

echo "<h2>2. Testing require_once...</h2>";
try {
    require_once __DIR__ . '/../../../src/config/database.php';
    echo "✓ database.php loaded<br>";
    
    require_once __DIR__ . '/../../../src/config/api_key.php';
    echo "✓ api_key.php loaded<br>";
    
    require_once __DIR__ . '/../../../src/lib/url_fetcher.php';
    echo "✓ url_fetcher.php loaded<br>";
    
    require_once __DIR__ . '/../../../src/utils/migrate_add_ai_relevance.php';
    echo "✓ migrate_add_ai_relevance.php loaded<br>";
    
    require_once __DIR__ . '/../../../src/lib/prompt_loader.php';
    echo "✓ prompt_loader.php loaded<br>";
} catch (Exception $e) {
    echo "✗ Error loading files: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<h2>3. Testing database connection...</h2>";
try {
    if (function_exists('getDbConnection')) {
        $conn = getDbConnection();
        if ($conn) {
            echo "✓ Database connection successful<br>";
            closeDbConnection($conn);
        } else {
            echo "✗ Database connection failed<br>";
        }
    } else {
        echo "✗ getDbConnection function not found<br>";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Testing ensureAiRelevanceColumns...</h2>";
try {
    if (function_exists('ensureAiRelevanceColumns')) {
        $conn = getDbConnection();
        if ($conn) {
            ensureAiRelevanceColumns($conn);
            echo "✓ ensureAiRelevanceColumns executed successfully<br>";
            closeDbConnection($conn);
        } else {
            echo "✗ Cannot get database connection<br>";
        }
    } else {
        echo "✗ ensureAiRelevanceColumns function not found<br>";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<h2>5. Testing prompt loader...</h2>";
try {
    if (function_exists('loadPrompt')) {
        $prompt = loadPrompt('relevance_rating', ['url' => 'test', 'text' => 'test']);
        echo "✓ Prompt loaded successfully (length: " . strlen($prompt) . " chars)<br>";
    } else {
        echo "✗ loadPrompt function not found<br>";
    }
} catch (Exception $e) {
    echo "✗ Error loading prompt: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<h2>6. Testing temp directory...</h2>";
$temp_dir = sys_get_temp_dir();
echo "Temp directory: $temp_dir<br>";
echo "Writable: " . (is_writable($temp_dir) ? "✓ Yes" : "✗ No") . "<br>";

echo "<h2>Done!</h2>";
?>

