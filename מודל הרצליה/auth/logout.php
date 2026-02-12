<?php
session_start();
require_once(__DIR__ . '/../db/connection.php');

if (isset($_SESSION['user_id'])) {
    // רישום ב-audit log
    $pdo = getDB();
    $auditStmt = $pdo->prepare("
        INSERT INTO audit_log (user_id, action_type, entity_type, entity_id, ip_address, user_agent)
        VALUES (?, 'LOGOUT', 'user', ?, ?, ?)
    ");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['user_id'],
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

session_destroy();
header('Location: login.php');
exit;
?>

