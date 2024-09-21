<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



$method = $_SERVER['REQUEST_METHOD'];

if ($method == "OPTIONS") {
    die();
}


if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Bad Reqeust detected. HTTP method should be DELETE',
    ]);
    exit;
endif;

require '../db_connect.php';
$database = new Operations();
$conn = $database->dbConnection();

$data = json_decode(file_get_contents("php://input"));
//echo $data = file_get_contents("php://input");

$id =  $_GET['id'];



if (!isset($id)) {
    echo json_encode(['success' => 0, 'message' => 'Please provide the category ID.']);
    exit;
}

try {

    $fetch_category = "SELECT * FROM `categories` WHERE id=:id";
    $fetch_stmt = $conn->prepare($fetch_category);
    $fetch_stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $fetch_stmt->execute();

    if ($fetch_stmt->rowCount() > 0) :

        $delete_category = "DELETE FROM `categories` WHERE id=:id";
        $delete_category_stmt = $conn->prepare($delete_category);
        $delete_category_stmt->bindValue(':id', $id,PDO::PARAM_INT);

        if ($delete_category_stmt->execute()) {

            echo json_encode([
                'success' => 1,
                'message' => 'Record Deleted successfully'
            ]);
            exit;
        }

        echo json_encode([
            'success' => 0,
            'message' => 'Could not delete. Something went wrong.'
        ]);
        exit;

    else :
        echo json_encode(['success' => 0, 'message' => 'Invalid ID. No categories found by the ID.']);
        exit;
    endif;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
    exit;
}