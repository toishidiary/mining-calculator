<?php
header('Content-Type: application/json');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Ошибка подключения к Binance']);
    curl_close($ch);
    exit;
}

curl_close($ch);
$data = json_decode($response, true);

if (isset($data['price'])) {
    echo json_encode(['btc_price' => floatval($data['price'])]);
} else {
    echo json_encode(['error' => 'Ошибка при получении курса BTC']);
}
