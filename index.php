<?php

header("Access-Control-Allow-Origin: *"); // Allow API calls from any domain (optional)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

/** Only allow POST requests **/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only POST requests are allowed"]);
    exit;
}

/** Get JSON input **/
$input = json_decode(file_get_contents("php://input"), true);

/** Check if request type is set **/
if (!isset($input['action'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Missing 'action' parameter. Use 'send' or 'verify'."]);
    exit;
}

/** Initialize cURL **/
$ch = curl_init();
$token = 'eyJhbGciOiJIUzI1NiJ9.eyJpZGVudGlmaWVyIjoiVENJdFl2SDBWMVkzV2xYU1k1cXdKMGtQV0czOGZDUWciLCJleHAiOjE4OTk0NDA3NDYsImlhdCI6MTc0MTY3NDM0NiwianRpIjoiYzU3Mzg2OTUtZDY2MC00NzNkLTk5YzgtYTI1OTJkYWY0NzQ1In0.2NRNLH52FoJJ3psSXDmzk-dtZuVBAa2elwMKmErhg1Y'; // Replace with actual token

/** Handle OTP Sending **/
if ($input['action'] === 'send') {
    if (!isset($input['to']) || !isset($input['from']) || !isset($input['sender'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Missing required parameters for sending OTP"]);
        exit;
    }

    /** Base URL **/
    $url = 'https://api.afromessage.com/api/challenge';

    /** Extract values **/
    $callback = $input['callback'] ?? '';
    $from = $input['from'];
    $sender = $input['sender'];
    $to = $input['to'];
    $pre = isset($input['pr']) ? urlencode($input['pr']) : "";
    $post = isset($input['ps']) ? urlencode($input['ps']) : "";
    $sb = $input['sb'] ?? 0;
    $sa = $input['sa'] ?? 0;
    $ttl = $input['ttl'] ?? 0;
    $len = $input['len'] ?? 4;
    $t = $input['t'] ?? 0;

    /** Construct query string **/
    $query = http_build_query([
        'from' => $from,
        'sender' => $sender,
        'to' => $to,
        'pr' => $pre,
        'ps' => $post,
        'sb' => $sb,
        'sa' => $sa,
        'ttl' => $ttl,
        'len' => $len,
        't' => $t,
        'callback' => $callback
    ]);

    /** Configure request **/
    curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    /** Request headers **/
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    /** Send request **/
    $result = curl_exec($ch);

    /** Handle response **/
    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(["error" => curl_error($ch)]);
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $data = json_decode($result, true);

        if ($http_code === 200 && isset($data['acknowledge']) && $data['acknowledge'] === 'success') {
            echo json_encode(["message" => "OTP Sent Successfully", "response" => $data]);
        } else {
            http_response_code($http_code);
            echo json_encode(["error" => "OTP Sending Failed", "response" => $data]);
        }
    }
}

/** Handle OTP Verification **/
elseif ($input['action'] === 'verify') {
    if (!isset($input['to']) || !isset($input['code'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing 'to' or 'code' parameter for verification"]);
        exit;
    }

    /** Base URL **/
    $url = 'https://api.afromessage.com/api/verify';
    $to = $input['to'];
    $code = $input['code'];

    /** Configure request **/
    curl_setopt($ch, CURLOPT_URL, $url . '?to=' . $to . '&code=' . $code);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    /** Request headers **/
    $headers = ['Authorization: Bearer ' . $token];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    /** Send request **/
    $result = curl_exec($ch);

    /** Handle response **/
    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(["error" => curl_error($ch)]);
    } else {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $data = json_decode($result, true);

        if ($http_code === 200 && isset($data['acknowledge']) && $data['acknowledge'] === 'success') {
            echo json_encode(["message" => "OTP Verified Successfully", "response" => $data]);
        } else {
            http_response_code($http_code);
            echo json_encode(["error" => "OTP Verification Failed", "response" => $data]);
        }
    }
}

/** Invalid action **/
else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid action. Use 'send' or 'verify'."]);
}

/** Finish **/
curl_close($ch);
