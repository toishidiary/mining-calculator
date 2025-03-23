<?php
function getBtcPriceRub() {
    $apiUrl = "https://api.binance.com/api/v3/ticker/price?symbol=BTCRUB";
    $response = file_get_contents($apiUrl);

    if (!$response) {
        return false;
    }

    $data = json_decode($response, true);
    return floatval($data['price'] ?? 0);
}
