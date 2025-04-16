<?php
header('Content-Type: application/json');
$path = $_GET['route'] ?? '';

switch ($path) {
    case 'hello':
        echo json_encode(["message" => "Hello there!"]);
        break;
    case 'user':
        echo json_encode(["user" => "Seen Dev"]);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "sorry, not found"]);
}
