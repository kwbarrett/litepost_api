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

if (!isset($_POST['postData'])) {
    echo json_encode([
        'success' => 0,
        'message' => 'Please enter compulsory fields | Title, Permalink, Content, Category, Post Image'
    ]);
    exit;
}

$data = json_decode($_POST['postData']);

if (empty(trim($data->title)) || empty(trim($data->excerpt)) || empty(trim($data->content))) {
    echo json_encode([
        'success' => 0,
        'message' => 'Field cannot be empty. Please fill all the fields.'
    ]);
    exit;
}

try {
    $title = htmlspecialchars(trim($data->title));
    $permalink = htmlspecialchars( trim( $data->permalink ) );
    $content = htmlspecialchars(trim($data->content));
    $excerpt = htmlspecialchars(trim($data->excerpt));
    $category_id = $data->category->category_id;
    $postImgPath = ''; 
    $isFeatured = $data->isFeatured;
    $status = htmlspecialchars(trim($data->status));
    $views = $data->views;
    $datetime = $data->createdAt;
    $date = new DateTime($datetime);
    $createdAt = $date->format('Y-m-d');

    // Handle file upload
    if (isset($_FILES['postImg']) && $_FILES['postImg']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['postImg']['tmp_name'];
        $fileName = $_FILES['postImg']['name'];
        $uploadFileDir = '../uploaded_files/';
        
        $dest_path = $uploadFileDir . $fileName;
    
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $postImgPath =  "http://localhost/ang-blog-api/uploaded_files/$fileName";
        } else {
            echo json_encode(['success' => 0, 'message' => 'There was an error moving the uploaded file.', 'file_location' => $uploadFileDir]);
            exit;
        }
    } else {
        echo json_encode(['success' => 0, 'message' => 'File upload error: ' . $_FILES['postImg']['error']]);
        exit;
    }

    $query = "INSERT INTO `posts`(
                title,
                permalink,
                excerpt,
                category_id,
                postImgPath,
                content,
                isFeatured,
                `status`,
                views,
                createdAt
              ) 
              VALUES(
                :title,
                :permalink,
                :excerpt,
                :category_id,
                :postImgPath,
                :content,
                :isFeatured,
                :status,
                :views,
                :createdAt
              )";

    $stmt = $conn->prepare($query);

    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':permalink', $permalink, PDO::PARAM_STR);
    $stmt->bindValue(':excerpt', $excerpt, PDO::PARAM_STR);
    $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindValue(':content', $content, PDO::PARAM_STR);
    $stmt->bindValue(':postImgPath', $postImgPath, PDO::PARAM_STR);
    $stmt->bindValue(':isFeatured', $isFeatured, PDO::PARAM_BOOL);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':views', $views, PDO::PARAM_INT);
    $stmt->bindValue(':createdAt', $createdAt, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            'success' => 1,
            'message' => 'Data Inserted Successfully. File uploaded successfully.'
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