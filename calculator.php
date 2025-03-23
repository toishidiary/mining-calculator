<?php
header('Content-Type: application/json');

$hashrate = floatval($_POST['hashrate'] ?? 0); // MH/s
$power = floatval($_POST['power'] ?? 0); // Вт
$cost = floatval($_POST['cost'] ?? 0.1); // ₽/кВт⋅ч
$algo = $_POST['algo'] ?? 'etchash';

if ($hashrate <= 0 || $power <= 0) {
    echo json_encode(['error' => 'Неверные входные данные']);
    exit;
}

$apiUrl = "https://whattomine.com/coins.json";
$response = file_get_contents($apiUrl);

if (!$response) {
    echo json_encode(['error' => 'Ошибка обращения к API WhatToMine']);
    exit;
}

$data = json_decode($response, true);
if (!isset($data['coins'])) {
    echo json_encode(['error' => 'Неверный ответ от API']);
    exit;
}

$results = [];

foreach ($data['coins'] as $coinName => $coin) {
    if (strtolower($coin['algorithm']) === strtolower($algo) && $coin['tag'] !== 'NICEHASH') {
        if (!isset($coin['btc_price']) || !isset($coin['btc_revenue'])) {
            continue;
        }

        $profitPerDay = $coin['btc_revenue'] * $coin['btc_price'];
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

usort($results, fn($a, $b) => $b['net_profit'] <=> $a['net_profit']);

echo json_encode([
    'success' => true,
    'results' => $results
]);
