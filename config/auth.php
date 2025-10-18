<?php

// Helper functions for authentication

function checkBearerToken($conn) {
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["message" => "Authorization header missing"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $headers['Authorization']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(403);
        echo json_encode(["message" => "Invalid token"]);
        exit;
    }

    return $user;
}

function generateToken() {
    return bin2hex(random_bytes(16));
}
?>
