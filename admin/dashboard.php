<?php
require_once '../config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$settings = getSettings();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $apiUrl = trim($_POST['api_url'] ?? '');
        $game = trim($_POST['default_game'] ?? '');
        $duration = intval($_POST['default_duration'] ?? 5);
        $deviceLimit = intval($_POST['default_device_limit'] ?? 1);
        $brandName = trim($_POST['brand_name'] ?? 'HEX CHEATS XOS');
        
        if (empty($apiUrl) || empty($game)) {
            $error = 'API URL and Game name are required';
        } else {
            updateSetting('api_url', $apiUrl);
            updateSetting('default_game', $game);
            updateSetting('default_duration', $duration);
            updateSetting('default_device_limit', $deviceLimit);
            updateSetting('brand_name', $brandName);
            $message = 'Settings updated successfully!';
            $settings = getSettings();
        }
    }
    
    if (isset($_POST['logout'])) {
        logAdminAction($_SESSION['admin_id'], 'logout');
        session_destroy();
        redirect('login.php');
    }
}

$licenses = getLicenses();
$stats = getLicenseStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0a0a0a;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: linear-gradient(145deg, #1a1a2e, #0f0f1f);
            border: 1px solid rgba(0, 212, 255, 0.15);
            border-radius: 16px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 { color: #00d4ff; font-size: 1.2rem; font-weight: 700; letter-spacing: 2px; margin: 0; }
        .header h1 i { margin-right: 10px; }
        .user-info { color: rgba(255,255,255,0.4); font-size: 0.8rem; display: flex; align-items: center; gap: 15px; }
        .btn-danger {
            padding: 8px 16px;
            background: #ff4444;
            border: none;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-danger:hover { background: #cc0000; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-box {
            background: rgba(0, 212, 255, 0.03);
            border: 1px solid rgba(0, 212, 255, 0.08);
            border-radius: 14px;
            padding: 18px 20px;
            text-align: center;
        }
        .stat-box .number { font-size: 2rem; font-weight: 700; color: #00d4ff; }
        .stat-box .label { font-size: 0.6rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 2px; margin-top: 4px; }
        .card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(0, 212, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .card h3 { color: #00d4ff; font-size: 0.9rem; font-weight: 700; letter-spacing: 2px; margin-bottom: 15px; }
        .card h3 i { margin-right: 8px; }
        .form-control {
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(0, 212, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            padding: 10px 14px;
            font-size: 0.8rem;
            width: 100%;
            margin-bottom: 10px;
        }
        .form-control:focus {
            background: rgba(0,0,0,0.6);
            border-color: #00d4ff;
            color: #fff;
            outline: none;
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.05);
        }
        .btn-primary {
            padding: 10px 20px;
            background: linear-gradient(90deg, #00d4ff, #7b2ffc);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 0 30px rgba(0, 212, 255, 0.15); }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        .table-responsive::-webkit-scrollbar { width: 4px; }
        .table-responsive::-webkit-scrollbar-thumb { background: rgba(0, 212, 255, 0.2); border-radius: 10px; }
        table { width: 100%; font-size: 0.75rem; border-collapse: collapse; }
        th { color: #00d4ff; text-align: left; padding: 10px; border-bottom: 1px solid rgba(0,212,255,0.1); font-weight: 600; letter-spacing: 1px; }
        td { padding: 10px; border-bottom: 1px solid rgba(0,212,255,0.05); }
        .badge {
            padding: 3px 14px;
            border-radius: 20px;
            font-size: 0.6rem;
            font-weight: 600;
        }
        .badge-active { background: rgba(0,255,100,0.15); color: #00ff64; }
        .badge-inactive { background: rgba(255,0,0,0.15); color: #ff4444; }
        .badge-used { background: rgba(255,193,7,0.15); color: #ffc107; }
        .badge-expired { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.3); }
        .row-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        .alert {
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            font-size: 0.8rem;
        }
        .alert-success { background: rgba(0,255,100,0.05); border: 1px solid rgba(0,255,100,0.1); color: #00ff64; }
        .alert-danger { background: rgba(255,0,0,0.05); border: 1px solid rgba(255,0,0,0.1); color: #ff4444; }
        .key-code { color: #00d4ff; font-family: 'Courier New', monospace; font-size: 0.7rem; }
        @media (max-width: 768px) { .row-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-crown"></i> ADMIN DASHBOARD</h1>
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="logout" class="btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="number"><?php echo $stats['total'] ?? 0; ?></div>
                <div class="label">Total Licenses</div>
            </div>
            <div class="stat-box">
                <div class="number"><?php echo $stats['active'] ?? 0; ?></div>
                <div class="label">Active</div>
            </div>
            <div class="stat-box">
                <div class="number"><?php echo $stats['today'] ?? 0; ?></div>
                <div class="label">Generated Today</div>
            </div>
        </div>

        <div class="row-grid">
            <div>
                <div class="card">
                    <h3><i class="fas fa-cog"></i> Settings</h3>
                    <form method="POST">
                        <input type="text" name="api_url" class="form-control" placeholder="API URL" value="<?php echo htmlspecialchars($settings['api_url'] ?? ''); ?>" required>
                        <input type="text" name="default_game" class="form-control" placeholder="Default Game" value="<?php echo htmlspecialchars($settings['default_game'] ?? 'FFMax No Root'); ?>" required>
                        <input type="number" name="default_duration" class="form-control" placeholder="Duration (Hours)" value="<?php echo htmlspecialchars($settings['default_duration'] ?? '5'); ?>" required>
                        <input type="number" name="default_device_limit" class="form-control" placeholder="Device Limit" value="<?php echo htmlspecialchars($settings['default_device_limit'] ?? '1'); ?>" required>
                        <input type="text" name="brand_name" class="form-control" placeholder="Brand Name" value="<?php echo htmlspecialchars($settings['brand_name'] ?? 'HEX CHEATS XOS'); ?>" required>
                        <button type="submit" name="update_settings" class="btn-primary"><i class="fas fa-save"></i> Save Settings</button>
                    </form>
                </div>
            </div>
            <div>
                <div class="card">
                    <h3><i class="fas fa-key"></i> Licenses</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Game</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($licenses as $l): ?>
                                    <tr>
                                        <td class="key-code"><?php echo htmlspecialchars($l['license_key']); ?></td>
                                        <td><?php echo htmlspecialchars($l['game']); ?></td>
                                        <td><span class="badge badge-<?php echo $l['status']; ?>"><?php echo strtoupper($l['status']); ?></span></td>
                                        <td style="font-size:0.6rem;color:rgba(255,255,255,0.3);"><?php echo substr($l['created_at'], 0, 10); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($licenses)): ?>
                                    <tr><td colspan="4" style="text-align:center;color:rgba(255,255,255,0.2);padding:30px;">No licenses generated yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
