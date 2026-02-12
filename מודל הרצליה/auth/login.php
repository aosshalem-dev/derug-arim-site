<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$error = '';

// ניסיון לטעון את connection רק כשצריך
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once(__DIR__ . '/../db/connection.php');
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // בדיקת סיסמה בלבד - כל מייל מתקבל
        if ($email && $password) {
            $pdo = getDB();
            
            // בדיקת סיסמה - gnostocracy7654
            $correctPassword = 'gnostocracy7654';
            if ($password === $correctPassword) {
                // בדיקה אם המשתמש כבר קיים
                $stmt = $pdo->prepare("SELECT id, email, password_hash, role FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    // יצירת משתמש חדש אם לא קיים
                    $passwordHash = password_hash($correctPassword, PASSWORD_DEFAULT);
                    $insertStmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'editor')");
                    $insertStmt->execute([$email, $passwordHash]);
                    $userId = $pdo->lastInsertId();
                    
                    $user = [
                        'id' => $userId,
                        'email' => $email,
                        'role' => 'editor'
                    ];
                } else {
                    // עדכון זמן כניסה אחרונה
                    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                }
                
                // שמירת session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'] ?? 'editor';
                
                // רישום ב-audit log (לא חובה - אם נכשל, ממשיכים)
                try {
                    $auditStmt = $pdo->prepare("
                        INSERT INTO audit_log (user_id, action_type, entity_type, entity_id, ip_address, user_agent)
                        VALUES (?, 'LOGIN', 'user', ?, ?, ?)
                    ");
                    $auditStmt->execute([
                        $user['id'],
                        $user['id'],
                        $_SERVER['REMOTE_ADDR'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]);
                } catch (Exception $e) {
                    // התעלם משגיאות audit log
                }
                
                header('Location: ../pages/index.php');
                exit;
            } else {
                $error = 'סיסמה שגויה';
            }
        } else {
            $error = 'נא למלא את כל השדות';
        }
    }
} catch (Exception $e) {
    $error = 'שגיאה: ' . $e->getMessage();
}

// אם כבר מחובר, הפניה לדף ראשי
if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>כניסה למערכת - מיפוי קשרים</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #005a87;
        }
        .error {
            background: #ffe6e6;
            color: #d00;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
        .info {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>כניסה למערכת מיפוי קשרים</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">מייל:</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">סיסמה:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">התחבר</button>
        </form>
    </div>
</body>
</html>
