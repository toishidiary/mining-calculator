<?php
header('Content-Type: application/json');
require_once 'get_btc_price.php';

// User inputs
$hashrate = floatval($_POST['hashrate'] ?? 0);
$power = floatval($_POST['power'] ?? 0);
$cost = floatval($_POST['cost'] ?? 0.1);
$algo = strtolower($_POST['algo'] ?? 'etchash');

if ($hashrate <= 0 || $power <= 0) {
    echo json_encode(['error' => 'Invalid input data']);
    exit;
}

// BTC to RUB price from CoinGecko
$btcToRub = getBtcPriceRub();
if (!$btcToRub) {
    echo json_encode(['error' => 'Failed to fetch BTC price from CoinGecko']);
    exit;
}

// Reference hashrate per algorithm (used to scale revenue properly)
$algoReferenceHashrates = [
    'etchash' => 100,       // MH/s
    'ethash'  => 100,       // MH/s
    'kawpow'  => 1000,      // MH/s (or 1 GH/s)
    'sha-256' => 1000000    // MH/s (or 1 TH/s)
];

$referenceHashrate = $algoReferenceHashrates[$algo] ?? 100;

// Fetch WhatToMine data
$apiUrl = "https://whattomine.com/coins.json";
$context = stream_context_create([
    'http' => ['timeout' => 10]
]);
$response = file_get_contents($apiUrl, false, $context);

if (!$response) {
    echo json_encode(['error' => 'Failed to fetch data from WhatToMine']);
    exit;
}

$data = json_decode($response, true);
if (!isset($data['coins'])) {
    echo json_encode(['error' => 'Invalid API response']);
    exit;
}

$results = [];

foreach ($data['coins'] as $coinName => $coin) {
    if (
        strtolower($coin['algorithm']) === $algo &&
        isset($coin['btc_revenue']) &&
        $coin['btc_revenue'] > 0
    ) {
        // Scale revenue to user hashrate
        $scaledRevenue = $coin['btc_revenue'] * ($hashrate / $referenceHashrate);
        $profitPerDay = $scaledRevenue * $btcToRub;
        $electricityCost = ($power / 1000) * 24 * $cost;
        $netProfit = $profitPerDay - $electricityCost;

        $results[] = [
            'coin' => $coinName,
            'profit_per_day' => round($profitPerDay, 2),
            'electricity_cost' => round($electricityCost, 2),
            'net_profit' => round($netProfit, 2),
            'tag' => $coin['tag'] ?? ''
        ];
    }
}

// Sort by profitability
usort($results, fn($a, $b) => $b['net_profit'] <=> $a['net_profit']);

echo json_encode([
    'success' => true,
    'results' => $results
]);
