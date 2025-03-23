<?php
function getBtcPriceRub() {
    $url = 'https://api.binance.com/api/v3/ticker/price?symbol=BTCRUB';
    $json = file_get_contents($url);
    if ($json === false) return null;

    $data = json_decode($json, true);
    return isset($data['price']) ? floatval($data['price']) : null;
}
?>
