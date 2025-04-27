<?php

/**
 * Logs visitor information to the database.
 *
 * @return void
 */
function logVisitorInfo(): void
{
    $db = new Database();
    $clientInfo = getClientInfo();
    $ipCountry = getIpCountry($clientInfo['ip']);

    $existingVisitor = $db->getRow(
        'SELECT visit_count FROM visitor_info WHERE ip_address = ?',
        [$clientInfo['ip']]
    );

    if ($existingVisitor) {
        $visitCount = (int)$existingVisitor->visit_count + 1;
        $db->update(
            'visitor_info',
            ['visit_count' => $visitCount],
            ['ip_address' => $clientInfo['ip']]
        );
    } else {
        $db->insert(
            'visitor_info',
            [
                'ip_address' => $clientInfo['ip'],
                'ip_country' => $ipCountry,
                'browser' => $clientInfo['browser'],
                'language' => $clientInfo['language'],
                'referer' => $clientInfo['referer'],
                'device' => $clientInfo['device'],
                'visit_count' => 1
            ]
        );
    }
}

/**
 * Returns client information as an array.
 *
 * @return array{
 *     ip: string,
 *     browser: string,
 *     language: string,
 *     referer: string,
 *     device: string
 * }
 */
function getClientInfo(): array
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    return [
        'ip'       => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        'browser'  => $userAgent,
        'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown',
        'referer'  => $_SERVER['HTTP_REFERER'] ?? 'Direct',
        'device'   => detectDevice($userAgent)
    ];
}

/**
 * Returns country and city for a given IP address.
 *
 * @param string $ip
 * @return string
 */
function getIpCountry(string $ip): string
{
    $url = "http://ip-api.com/php/{$ip}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response === false) {
        return 'Unknown';
    }
    $data = @unserialize($response);
    if (is_array($data) && isset($data['country'], $data['city'])) {
        return "{$data['country']}-{$data['city']}";
    }
    return 'Unknown';
}

/**
 * Detects device type from user agent string.
 *
 * @param string $userAgent
 * @return string
 */
function detectDevice(string $userAgent): string
{
    $userAgent = strtolower($userAgent);
    if (strpos($userAgent, 'windows') !== false) return 'Windows';
    if (strpos($userAgent, 'mac') !== false) return 'Mac';
    if (strpos($userAgent, 'android') !== false) return 'Android';
    if (strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipad') !== false) return 'iOS';
    return 'Unknown';
}

?>