<?php
function getBtcPriceRub() {
    $apiUrl = "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=rub";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10 // Set a timeout for the request
        ]
    ]);
    $response = file_get_contents($apiUrl, false, $context);

    if (!$response) {
        error_log("Failed to fetch BTC price from CoinGecko: " . print_r(error_get_last(), true));
        return false;
    }

    $data = json_decode($response, true);
    if (!isset($data['bitcoin']['rub'])) {
        error_log("Invalid response from CoinGecko API: " . $response);
        return false;
    }

    return floatval($data['bitcoin']['rub']);
}
