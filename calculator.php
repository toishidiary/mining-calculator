<?php
header('Content-Type: application/json');

require_once 'get_btc_price.php';

// Validate input
$hashrate = floatval($_POST['hashrate'] ?? 0);
$power = floatval($_POST['power'] ?? 0);
$cost = floatval($_POST['cost'] ?? 0.1);
$algo = $_POST['algo'] ?? 'etchash';

if ($hashrate <= 0 || $power <= 0) {
    echo json_encode(['error' => 'Invalid input data']);
    exit;
}

// Get BTC price in RUB
$btcToRub = getBtcPriceRub();
if (!$btcToRub) {
    echo json_encode(['error' => 'Failed to fetch BTC price from Binance']);
    exit;
}

// Fetch data from WhatToMine API
$apiUrl = "https://whattomine.com/coins.json";
$context = stream_context_create([
    'http' => [
        'timeout' => 10 // Set a timeout for the request
    ]
]);
$response = file_get_contents($apiUrl, false, $context);

if (!$response) {
    echo json_encode(['error' => 'Failed to fetch data from WhatToMine API']);
    exit;
}

$data = json_decode($response, true);
if (!isset($data['coins'])) {
    echo json_encode(['error' => 'Invalid response from WhatToMine API']);
    exit;
}

// Calculate profitability for each coin
$results = [];

foreach ($data['coins'] as $coinName => $coin) {
    if (strtolower($coin['algorithm']) === strtolower($algo) && isset($coin['btc_revenue'])) {
        $profitPerDay = $coin['btc_revenue'] * $btcToRub;
        $electricityCost = ($power / 1000) * 24 * $cost;
        $netProfit = $profitPerDay - $electricityCost;

        $results[] = [
            'coin' => $coinName,
            'profit_per_day' => round($profitPerDay, 2),
            'electricity_cost' => round($electricityCost, 2),
            'net_profit' => round($netProfit, 2),
            'tag' => $coin['tag']
        ];
    }
}

// Sort results by net profit in descending order
usort($results, fn($a, $b) => $b['net_profit'] <=> $a['net_profit']);

echo json_encode([
    'success' => true,
    'results' => $results
]);
