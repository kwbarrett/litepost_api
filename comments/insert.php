<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    die();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Bad Request! Only POST method is allowed',
    ]);
    exit;
}

require '../db_connect.php';
$database = new Operations();
$conn = $database->dbConnection();
$data = json_decode(file_get_contents("php://input"));

if (empty(trim($data->name)) || empty(trim($data->comment))) {
    echo json_encode([
        'success' => 0,
        'message' => 'Field cannot be empty. Please fill all the fields.'
    ]);
    exit;
}

try {
    $name = htmlspecialchars(trim($data->name));
    $comment = htmlspecialchars(trim($data->comment));
    $post_id = $data->post_id;
    $datetime = $data->createdAt;
    $date = new DateTime($datetime);
    $createdAt = $date->format('Y-m-d');

    $query = "INSERT INTO `comments`(
                `name`,
                `comment`,
                `post_id`,
                `createdAt`
              ) VALUES (
                :name,
                :comment,
                :post_id,
                :createdAt
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->bindValue(':createdAt', $createdAt, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Data Inserted Successfully.'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => 0,
        'message' => 'There is some problem in data inserting'
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode([
        'success' => 0,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    exit;
}
