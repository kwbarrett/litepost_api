<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

 

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "OPTIONS") {
    header("Access-Control-Allow-Origin: http://localhost:4300");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Bad Request!.Only POST method is allowed',
    ]);
    exit;
endif;
 
require '../db_connect.php';
$database = new Operations();
$conn = $database->dbConnection();
 
$data = json_decode(file_get_contents("php://input"));


//print_r($data);

if (!isset($data->category_name)) :
 
    echo json_encode([
        'success' => 0,
        'message' => 'Please enter required fileds |  Category Name',
    ]);
    exit;
 
elseif (empty(trim($data->category_name))) :
 
    echo json_encode([
        'success' => 0,
        'message' => 'Field cannot be empty. Please fill all the fields.',
    ]);
    exit;
 
endif;
 
try {
 
    $category_name = htmlspecialchars(trim($data->category_name));
 
    $query = "  INSERT INTO 
                    `categories`(
                        category_name
                        ) 
                    VALUES(
                        :category_name
                        )";
 
    $stmt = $conn->prepare($query);
 
    $stmt->bindValue(':category_name', $category_name, PDO::PARAM_STR);    

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
 
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
    exit;
}
