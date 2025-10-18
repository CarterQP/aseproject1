<?php

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

// config files
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

// get the request path
$request_path = $_SERVER['PATH_INFO'] ?? ($_GET['path'] ?? '');
$request = explode('/', trim($request_path, '/'));

// Route handling
if (!isset($request[0]) || $request[0] !== 'songs') {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET /songs
if ($method === 'GET' && count($request) === 1) {
    $stmt = $conn->prepare("SELECT * FROM songs");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// GET /songs/{id}
if ($method === 'GET' && isset($request[1]) && is_numeric($request[1])) {
    $id = $request[1];
    $stmt = $conn->prepare("SELECT * FROM songs WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

// POST /songs (secure) 
if ($method === 'POST' && count($request) === 1) {
    checkBearerToken($conn);

    $title = $_POST['title'] ?? '';
    $artist = $_POST['artist'] ?? '';
    $genre = $_POST['genre'] ?? '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $target_dir = __DIR__ . "/../uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO songs (title, artist, genre, filename) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $artist, $genre, $file_name]);
            echo json_encode(["message" => "Song uploaded successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to move uploaded file"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "No file uploaded"]);
    }
    exit;
}

// PUT /songs/{id} (secure)
if ($method === 'PUT' && isset($request[1]) && is_numeric($request[1])) {
    checkBearerToken($conn);

    parse_str(file_get_contents("php://input"), $put_vars);
    $id = $request[1];
    $title = $put_vars['title'] ?? '';
    $artist = $put_vars['artist'] ?? '';
    $genre = $put_vars['genre'] ?? '';

    $stmt = $conn->prepare("UPDATE songs SET title=?, artist=?, genre=? WHERE id=?");
    $stmt->execute([$title, $artist, $genre, $id]);
    echo json_encode(["message" => "Song updated"]);
    exit;
}

// DELETE /songs/{id} (secure)
if ($method === 'DELETE' && isset($request[1]) && is_numeric($request[1])) {
    checkBearerToken($conn);

    $id = $request[1];
    $stmt = $conn->prepare("DELETE FROM songs WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(["message" => "Song deleted"]);
    exit;
}

// GET /songs/artist/{name}
if ($method === 'GET' && isset($request[1]) && $request[1] === 'artist' && isset($request[2])) {
    $artist = $request[2];
    $stmt = $conn->prepare("SELECT * FROM songs WHERE artist=?");
    $stmt->execute([$artist]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// GET /songs/genre/{genre}
if ($method === 'GET' && isset($request[1]) && $request[1] === 'genre' && isset($request[2])) {
    $genre = $request[2];
    $stmt = $conn->prepare("SELECT * FROM songs WHERE genre=?");
    $stmt->execute([$genre]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// POST /songs/{id}/comment
if ($method === 'POST' && isset($request[1], $request[2]) && is_numeric($request[1]) && $request[2] === 'comment') {
    $song_id = $request[1];
    $data = json_decode(file_get_contents("php://input"), true);
    $comment = $data['comment'] ?? '';

    $stmt = $conn->prepare("INSERT INTO comments (song_id, comment) VALUES (?, ?)");
    $stmt->execute([$song_id, $comment]);
    echo json_encode(["message" => "Comment added"]);
    exit;
}

// GET /songs/title/{title}
if ($method === 'GET' && isset($request[1]) && $request[1] === 'title' && isset($request[2])) {
    $title = $request[2];
    $stmt = $conn->prepare("SELECT * FROM songs WHERE title=?");
    $stmt->execute([$title]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Just in case
http_response_code(404);
echo json_encode(["message" => "No Endpoint"]);
