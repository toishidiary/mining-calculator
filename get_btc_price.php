<?php
function getBtcPriceRub() {
    $apiUrl = "https://api.binance.com/api/v3/ticker/price?symbol=BTCRUB";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10 // Set a timeout for the request
        ]
    ]);
    $response = file_get_contents($apiUrl, false, $context);

    if (!$response) {
        error_log("Failed to fetch BTC price from Binance: " . print_r(error_get_last(), true));
        return false;
    }

    $data = json_decode($response, true);
    if (!isset($data['price'])) {
        error_log("Invalid response from Binance API: " . $response);
        return false;
    }

    return floatval($data['price']);
}
