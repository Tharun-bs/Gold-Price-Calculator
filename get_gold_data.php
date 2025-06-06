<?php
// Set content type to JSON
header('Content-Type: application/json');

$apiKey = "goldapi-2449msmbgszaxe-io";
$symbol = "XAU";
$curr = "INR";
$date = ""; // Leave empty for current price

$myHeaders = array(
    'x-access-token: ' . $apiKey,
    'Content-Type: application/json'
);

$curl = curl_init();

// Fix URL construction - remove extra slash and ensure proper format
$url = "https://www.goldapi.io/api/" . $symbol . "/" . $curr;
if (!empty($date)) {
    $url .= "/" . $date;
}

curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => $myHeaders,
    CURLOPT_TIMEOUT => 30, // Add timeout
    CURLOPT_SSL_VERIFYPEER => true, // Enable SSL verification
    CURLOPT_USERAGENT => 'Gold Calculator App 1.0' // Add user agent
));

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);

curl_close($curl);

// Enhanced error handling
if ($error) {
    echo json_encode([
        'error' => 'cURL Error: ' . $error,
        'debug' => 'Failed to connect to Gold API'
    ]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode([
        'error' => 'HTTP Error: ' . $httpCode,
        'debug' => 'API returned non-200 status code',
        'response' => $response
    ]);
    exit;
}

if (empty($response)) {
    echo json_encode([
        'error' => 'Empty response from API',
        'debug' => 'No data received from Gold API'
    ]);
    exit;
}

$data = json_decode($response, true);

// Check for JSON decode errors
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'error' => 'JSON decode error: ' . json_last_error_msg(),
        'debug' => 'Failed to parse API response',
        'raw_response' => $response
    ]);
    exit;
}

// Debug: Log the actual API response structure
error_log('Gold API Response: ' . print_r($data, true));

// Handle different possible response structures
if (isset($data['price'])) {
    // Convert from per ounce to per gram (1 ounce = 31.1035 grams)
    $pricePerGram = $data['price'] / 31.1035;
    echo json_encode([
        'rate' => round($pricePerGram, 2),
        'original_price' => $data['price'],
        'currency' => $curr,
        'timestamp' => isset($data['timestamp']) ? $data['timestamp'] : time()
    ]);
} elseif (isset($data['price_gram_24k'])) {
    // Some APIs return price per gram directly
    echo json_encode([
        'rate' => $data['price_gram_24k'],
        'currency' => $curr,
        'timestamp' => isset($data['timestamp']) ? $data['timestamp'] : time()
    ]);
} elseif (isset($data['ask'])) {
    // Handle ask/bid pricing
    $pricePerGram = $data['ask'] / 31.1035;
    echo json_encode([
        'rate' => round($pricePerGram, 2),
        'original_price' => $data['ask'],
        'currency' => $curr,
        'timestamp' => isset($data['timestamp']) ? $data['timestamp'] : time()
    ]);
} else {
    // Enhanced error with full response for debugging
    echo json_encode([
        'error' => 'Price not found in API response',
        'debug' => 'Unable to locate price field in response',
        'available_fields' => array_keys($data),
        'full_response' => $data
    ]);
}
?>