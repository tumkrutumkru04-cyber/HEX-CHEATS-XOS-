<?php
require_once 'config.php';

$settings = getSettings();
$brandName = getBrandName();
$generatedKey = null;
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $game = $settings['default_game'] ?? 'FFMax No Root';
    $deviceLimit = 1;
    $duration = 5;
    $keyType = 'FREE';
    
    $result = generateLicenseKey(1);
    
    if ($result && isset($result['success']) && $result['success'] && !empty($result['keys'])) {
        $key = $result['keys'][0];
        if (saveLicense($key, $game, $deviceLimit, $duration, $keyType)) {
            $success = "License generated successfully!";
            $generatedKey = $key;
        } else {
            $error = "Failed to save license to database.";
        }
    } else {
        $error = "Failed to generate license. Please try again.";
    }
}

$recentLicenses = getLicenses(5);
$stats = getLicenseStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $brandName; ?> - Get Free Key - Global</title>
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
        .main-container {
            width: 100%;
            max-width: 500px;
        }
        .card {
            background: linear-gradient(145deg, #1a1a2e, #0f0f1f);
            border: 2px solid rgba(0, 212, 255, 0.3);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 0 60px rgba(0, 212, 255, 0.05);
        }
        .card-header {
            background: rgba(0, 212, 255, 0.05);
            border-bottom: 1px solid rgba(0, 212, 255, 0.1);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            color: #fff;
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 1px;
        }
        .card-title small {
            color: #7b2ffc;
            font-size: 0.7rem;
            font-weight: 400;
            margin-left: 8px;
        }
        .badge-global {
            background: rgba(0, 212, 255, 0.15);
            border: 1px solid rgba(0, 212, 255, 0.3);
            color: #00d4ff;
            font-size: 0.65rem;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 600;
        }
        .card-body {
            padding: 25px;
        }
        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-row .full {
            grid-column: 1 / -1;
        }
        .info-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .info-label {
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }
        .info-value {
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
        }
        .info-value.game-name {
            color: #7b2ffc;
            font-size: 1.1rem;
        }
        .info-value.highlight {
            color: #00d4ff;
        }
        .btn-generate {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #00d4ff, #7b2ffc);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 40px rgba(0, 212, 255, 0.2);
        }
        .btn-generate:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .btn-generate i {
            margin-right: 8px;
        }
        .key-display {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
        }
        .key-display .label {
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.55rem;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .key-display .key {
            color: #00d4ff;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin: 8px 0;
        }
        .key-display .status {
            display: inline-block;
            padding: 3px 16px;
            background: rgba(0, 255, 100, 0.1);
            border: 1px solid rgba(0, 255, 100, 0.3);
            border-radius: 20px;
            color: #00ff64;
            font-size: 0.6rem;
            letter-spacing: 1px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-bottom: 15px;
        }
        .alert-success {
            background: rgba(0, 255, 100, 0.08);
            border: 1px solid rgba(0, 255, 100, 0.2);
            color: #00ff64;
        }
        .alert-danger {
            background: rgba(255, 0, 0, 0.08);
            border: 1px solid rgba(255, 0, 0, 0.2);
            color: #ff4444;
        }
        .recent-section {
            margin-top: 20px;
        }
        .recent-title {
            color: rgba(255, 255, 255, 0.2);
            font-size: 0.55rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }
        .recent-item {
            background: rgba(0, 0, 0, 0.3);
            padding: 8px 14px;
            border-radius: 8px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 2px solid rgba(0, 212, 255, 0.15);
        }
        .recent-item .key {
            color: rgba(0, 212, 255, 0.8);
            font-size: 0.7rem;
            font-family: 'Courier New', monospace;
        }
        .recent-item .status {
            font-size: 0.55rem;
            color: rgba(255, 255, 255, 0.3);
        }
        .admin-link {
            text-align: center;
            margin-top: 20px;
        }
        .admin-link a {
            color: rgba(255, 255, 255, 0.1);
            font-size: 0.5rem;
            text-decoration: none;
            letter-spacing: 2px;
            transition: all 0.3s ease;
        }
        .admin-link a:hover {
            color: rgba(0, 212, 255, 0.3);
        }
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s ease-in-out infinite;
            vertical-align: middle;
            margin-right: 8px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @media (max-width: 480px) {
            .card-body { padding: 18px; }
            .card-header { padding: 15px 18px; }
            .card-title { font-size: 1rem; }
            .info-row { grid-template-columns: 1fr; gap: 10px; }
            .info-value.game-name { font-size: 0.95rem; }
            .key-display .key { font-size: 0.95rem; }
            .btn-generate { font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Create Free License
                    <small>Mod Menu Only</small>
                </h3>
                <span class="badge-global">Global</span>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="info-row">
                    <div class="info-item full">
                        <div class="info-label">Game</div>
                        <div class="info-value game-name"><?php echo htmlspecialchars($settings['default_game'] ?? 'FFMax No Root'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Devices</div>
                        <div class="info-value">1 device</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Duration</div>
                        <div class="info-value highlight"><?php echo $settings['default_duration'] ?? '5'; ?> Hours</div>
                    </div>
                </div>

                <form method="POST">
                    <button type="submit" name="generate" class="btn-generate" id="generateBtn">
                        <i class="fas fa-bolt"></i> Generate
                    </button>
                </form>

                <?php if ($generatedKey): ?>
                    <div class="key-display">
                        <div class="label">Your License Key</div>
                        <div class="key"><?php echo htmlspecialchars($generatedKey); ?></div>
                        <div class="status"><i class="fas fa-circle" style="font-size:0.4rem;margin-right:6px;"></i> ACTIVE</div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($recentLicenses)): ?>
                    <div class="recent-section">
                        <div class="recent-title"><i class="fas fa-history"></i> Recent Generated</div>
                        <?php foreach (array_slice($recentLicenses, 0, 3) as $license): ?>
                            <div class="recent-item">
                                <span class="key"><?php echo htmlspecialchars($license['license_key']); ?></span>
                                <span class="status"><?php echo strtoupper($license['status']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="admin-link">
                    <a href="admin/login.php"><i class="fas fa-cog"></i> ADMIN</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('generateBtn').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner"></span> Generating...';
        });

        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(el) {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
