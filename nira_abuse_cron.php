<?php

set_time_limit(3600);

$url = 'https://DEPLOYED_ABUSE_ENDPOINT/api/check-netbeacon';

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 3600,
    CURLOPT_CONNECTTIMEOUT => 600,
    CURLOPT_HTTPGET => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'NIRA-CRON-NETBEACON: Netbeacon_cron',
    ],
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$response = curl_exec($ch);

if ($response === false) {
    echo 'cURL Error: ' . curl_error($ch) . PHP_EOL;
    curl_close($ch);
    exit(1);
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "HTTP Status: {$httpCode}" . PHP_EOL;
echo $response . PHP_EOL;