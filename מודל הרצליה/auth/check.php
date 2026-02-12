<?php
/**
 * בדיקת הרשאות - יש לכלול בתחילת כל דף שדורש כניסה
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    // קביעת נתיב נכון לפי מיקום הקובץ
    $loginPath = 'login.php';
    if (strpos($_SERVER['PHP_SELF'], '/api/') !== false) {
        $loginPath = '../auth/login.php';
    } elseif (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
        $loginPath = '../auth/login.php';
    }
    
    // אם זה קריאה מ-API, החזר JSON error
    if (strpos($_SERVER['PHP_SELF'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'redirect' => $loginPath]);
        exit;
    } else {
        header('Location: ' . $loginPath);
        exit;
    }
}

// פונקציה לבדיקת תפקיד
function requireRole($requiredRole) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $requiredRole) {
        header('Location: ../pages/index.php?error=insufficient_permissions');
        exit;
    }
}

// פונקציה לבדיקת admin
function requireAdmin() {
    requireRole('admin');
}

// פונקציה לקבלת ID המשתמש הנוכחי
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// פונקציה לקבלת מייל המשתמש הנוכחי
function getCurrentUserEmail() {
    return $_SESSION['user_email'] ?? null;
}
?>

