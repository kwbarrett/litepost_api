<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die();
}
if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Bad Request detected! Only PUT method is allowed',
    ]);
    exit;
}

require '../db_connect.php';
$database = new Operations();
$conn = $database->dbConnection();
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    echo json_encode(['success' => 0, 'message' => 'Please enter correct Post id.']);
    exit;
}

try {
    $update_query = "UPDATE `posts` SET views = views + 1 WHERE id = :id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindValue(':id', $data->id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => 1,
            'message' => 'Record updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Did not update. Something went wrong.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
}
