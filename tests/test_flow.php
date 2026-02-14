<?php

require __DIR__ . '/../vendor/autoload.php';

// Configuration
$baseUrl = 'http://localhost/gestion-citas-peluqueria/public';
$today = date('Y-m-d');
$testPhone = '600000' . rand(100, 999); 

echo "🚀 Iniciando Test de Integración: Flujo Completo (+ Patch Status)\n";
echo "📅 Fecha: $today\n";
echo "------------------------------------------------\n";

function call($method, $endpoint, $data = null, $cookieFile = null) {
    global $baseUrl;
    $ch = curl_init($baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

// 1. Get Slot (Retry logic for weekends/late hours)
$datesToTry = [$today, date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+2 days'))];
$slot = null;
$targetDate = null;

foreach ($datesToTry as $d) {
    echo "🔎 Buscando hueco para $d...\n";
    $res = call('GET', "/api/v1/availability?date=$d");
    if ($res['code'] === 200 && !empty($res['body']['slots'])) {
        $slot = $res['body']['slots'][0];
        $targetDate = $d;
        break;
    }
}

if (!$slot) die("❌ No slots found in the next 3 days.\n");

// 2. Book
$res = call('POST', '/api/v1/appointments', ['name'=>'Test','phone'=>$testPhone,'date'=>$targetDate,'time'=>$slot]);
$id = $res['body']['appointment_id'];
echo "✅ Reservado ID: $id ($targetDate $slot)\n";

// 3. Admin Login
$cookie = sys_get_temp_dir() . '/cookie_test.txt';
if (file_exists($cookie)) unlink($cookie);
call('POST', '/api/v1/auth/login', ['username'=>'admin','password'=>'admin'], $cookie);

// 4. Mark as Attended (PATCH)
echo "🔄 Marcando como 'attended' vía PATCH...\n";
$res = call('PATCH', "/api/v1/admin/appointments/$id/status", ['status'=>'attended'], $cookie);

if ($res['code'] === 200 && $res['body']['status'] === 'attended') {
    echo "✅ Estado actualizado a 'attended'.\n";
} else {
    echo "❌ Falló updateStatus. Code: {$res['code']}\n";
    print_r($res['body']);
    exit(1);
}

// 5. Verify in List
$res = call('GET', "/api/v1/admin/appointments?date=$targetDate", null, $cookie);
foreach($res['body'] as $a) {
    if ($a['id'] === $id && $a['status'] === 'attended') {
        echo "✅ Verificado en listado: Status es 'attended'.\n";
        break;
    }
}

// Cleanup
@unlink($cookie);
