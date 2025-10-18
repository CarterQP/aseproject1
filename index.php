<?php
// index.php - Main entry point
header("Content-Type: application/json");

require_once "config/db.php";
require_once "config/auth.php";

$path = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

if (isset($path[0]) && $path[0] === 'songs') {
    require "api/songs.php";
} else {
    http_response_code(404);
    echo json_encode(["message" => "No Enpoint"]);
}

