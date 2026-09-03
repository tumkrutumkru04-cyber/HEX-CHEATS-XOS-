<?php
// ============================================================
//  SUPABASE REST API CONFIGURATION
// ============================================================

define('SUPABASE_URL', 'https://zexjuvbzoltajftbunnx.supabase.co');
define('SUPABASE_KEY', 'sb_publishable_Gm7etPKXTpP-hb5nllygBw_pGP_kX8h');

define('DEFAULT_API_URL', 'https://hex-protocol-git-production.up.railway.app/api/generate/5hour');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function supabaseRequest($method, $endpoint, $data = null, $params = null) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    
    if ($params) {
        $url .= '?' . http_build_query($params);
    }
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error];
    }
    
    return json_decode($response, true);
}

function getSettings() {
    $result = supabaseRequest('GET', 'admin_settings', null, ['select' => '*']);
    if (isset($result['error']) || empty($result)) {
        return [];
    }
    
    $settings = [];
    foreach ($result as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function updateSetting($key, $value) {
    $data = ['setting_value' => $value];
    return supabaseRequest('PATCH', 'admin_settings', $data, ['setting_key' => 'eq.' . $key]);
}

function getBrandName() {
    $settings = getSettings();
    return $settings['brand_name'] ?? 'HEX CHEATS XOS';
}

function generateLicenseKey($count = 1) {
    $settings = getSettings();
    $apiUrl = $settings['api_url'] ?? DEFAULT_API_URL;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['count' => $count]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        logAPICall('success', $response);
        return $data;
    } else {
        logAPICall('error', 'HTTP: ' . $httpCode);
        return false;
    }
}

function saveLicense($key, $game, $deviceLimit, $duration, $keyType) {
    $data = [
        'license_key' => $key,
        'game' => $game,
        'device_limit' => $deviceLimit,
        'duration' => $duration,
        'key_type' => $keyType,
        'status' => 'active'
    ];
    return supabaseRequest('POST', 'licenses', $data);
}

function getLicenses($limit = null) {
    $params = ['select' => '*', 'order' => 'created_at.desc'];
    if ($limit) {
        $params['limit'] = intval($limit);
    }
    $result = supabaseRequest('GET', 'licenses', null, $params);
    if (isset($result['error']) || !is_array($result)) {
        return [];
    }
    return $result;
}

function getLicenseStats() {
    $licenses = getLicenses();
    $total = count($licenses);
    $active = 0;
    $today = 0;
    $todayDate = date('Y-m-d');
    
    foreach ($licenses as $l) {
        if (isset($l['status']) && $l['status'] === 'active') {
            $active++;
        }
        if (isset($l['created_at']) && substr($l['created_at'], 0, 10) === $todayDate) {
            $today++;
        }
    }
    
    return [
        'total' => $total,
        'active' => $active,
        'today' => $today
    ];
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function logAPICall($status, $data) {
    $logData = [
        'request_type' => 'generate',
        'response_data' => is_string($data) ? $data : json_encode($data),
        'status' => $status
    ];
    return supabaseRequest('POST', 'api_logs', $logData);
}

function logAdminAction($adminId, $action, $details = null, $ip = null) {
    $logData = [
        'admin_id' => $adminId,
        'action' => $action,
        'details' => $details,
        'ip_address' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
    ];
    return supabaseRequest('POST', 'admin_logs', $logData);
}
?>
