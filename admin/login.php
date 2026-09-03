<?php
require_once '../config.php';

if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $users = supabaseRequest('GET', 'admin_users', null, ['select' => '*', 'username' => 'eq.' . $username]);
    
    if (!empty($users) && isset($users[0])) {
        $user = $users[0];
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            logAdminAction($user['id'], 'login');
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0a0a0a;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: linear-gradient(145deg, #1a1a2e, #0f0f1f);
            border: 2px solid rgba(0, 212, 255, 0.3);
            border-radius: 20px;
            padding: 40px 35px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 0 60px rgba(0, 212, 255, 0.05);
        }
        .login-card h2 {
            color: #00d4ff;
            text-align: center;
            font-weight: 700;
            letter-spacing: 3px;
            margin-bottom: 30px;
            font-size: 1.3rem;
        }
        .login-card h2 i {
            margin-right: 10px;
        }
        .form-control {
            background: rgba(0,0,0,0.5);
            border: 1px solid rgba(0, 212, 255, 0.15);
            color: #fff;
            border-radius: 12px;
            padding: 13px 16px;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }
        .form-control:focus {
            background: rgba(0,0,0,0.7);
            border-color: #00d4ff;
            color: #fff;
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.05);
        }
        .form-control::placeholder {
            color: rgba(255,255,255,0.2);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #00d4ff, #7b2ffc);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 3px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(0, 212, 255, 0.2);
        }
        .error {
            color: #ff4444;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(255,0,0,0.05);
            border-radius: 10px;
            font-size: 0.8rem;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.15);
            font-size: 0.6rem;
            letter-spacing: 1px;
        }
        .icon-big {
            text-align: center;
            font-size: 2.5rem;
            color: #00d4ff;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="icon-big"><i class="fas fa-crown"></i></div>
        <h2><i class="fas fa-shield-alt"></i> ADMIN</h2>
        
        <?php if ($error): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button type="submit" class="btn-login"><i class="fas fa-lock"></i> LOGIN</button>
        </form>
        
        <div class="footer">Default: admin / admin123</div>
    </div>
</body>
</html>
