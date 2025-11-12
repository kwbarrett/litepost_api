<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ERROR);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Bad Request Detected! Only GET method is allowed',
    ]);
    exit;
}

require '../db_connect.php';
$database = new Operations();
$conn = $database->dbConnection();

$id = null;
$post_id = null;

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
        'options' => [
            'default' => 'all_comments',
            'min_range' => 1
        ]
    ]);
} else if (isset($_GET['post_id'])) {
    $post_id = filter_var($_GET['post_id'], FILTER_VALIDATE_INT, [
        'options' => [
            'default' => 'comments_by_post',
            'min_range' => 1
        ]
    ]);
}

try {
    if (is_numeric($id)) {
        $sql = "SELECT * FROM `comments` WHERE id='$id' ORDER BY createdAt DESC";
    } else if (is_numeric($post_id)) {
        $sql = "SELECT * FROM `comments` WHERE post_id='$post_id' ORDER BY createdAt DESC";
    } else {
        $sql = "SELECT * FROM `comments` ORDER BY createdAt DESC";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $data = is_numeric($id) ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => 1,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'No Record Found!'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    exit;
}
