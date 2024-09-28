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
        'message' => 'Please enter compulsory fields | Title, Permalink, Content, Category'
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
    $postId = $data->id;
    $title = htmlspecialchars(trim($data->title));
    $permalink = htmlspecialchars(trim($data->permalink));
    $content = htmlspecialchars(trim($data->content));
    $excerpt = htmlspecialchars(trim($data->excerpt));
    $category_id = $data->category->category_id;
    $postImgPath = $data->postImgPath; 
    $isFeatured = $data->isFeatured;
    $status = htmlspecialchars(trim($data->status));
    $views = $data->views;
    $datetime = $data->createdAt;
    $date = new DateTime($datetime);
    $createdAt = $date->format('Y-m-d');

    // Handle file upload if a new file is provided
    if (isset($_FILES['postImg']) && $_FILES['postImg']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['postImg']['tmp_name'];
        $fileName = $_FILES['postImg']['name'];
        $uploadFileDir = '../uploaded_files/';
        $dest_path = $uploadFileDir . $fileName;
    
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $postImgPath = "http://localhost/ang-blog-api/uploaded_files/$fileName";
        }
    }
    if( $postImgPath === '' ){
        $query = "  UPDATE `posts` SET
                        title = :title,
                        permalink = :permalink,
                        excerpt = :excerpt,
                        category_id = :category_id,
                        content = :content,
                        isFeatured = :isFeatured,
                        `status` = :status,
                        views = :views,
                        createdAt = :createdAt
                    WHERE id = :id";
    }else{
        $query = "  UPDATE 
                        `posts` 
                    SET
                        title = :title,
                        permalink = :permalink,
                        excerpt = :excerpt,
                        category_id = :category_id,
                        postImgPath = :postImgPath,
                        content = :content,
                        isFeatured = :isFeatured,
                        `status` = :status,
                        views = :views,
                        createdAt = :createdAt
                    WHERE id = :id";
    }
    

    $stmt = $conn->prepare($query);

    $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':permalink', $permalink, PDO::PARAM_STR);
    $stmt->bindParam(':excerpt', $excerpt, PDO::PARAM_STR);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    if(  $postImgPath !== ''){
        $stmt->bindParam(':postImgPath', $postImgPath, PDO::PARAM_STR);
    }
    
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->bindParam(':isFeatured', $isFeatured, PDO::PARAM_BOOL);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':views', $views, PDO::PARAM_INT);
    $stmt->bindParam(':createdAt', $createdAt, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => 1,
            'message' => 'Post updated successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => 0,
            'message' => 'Post update failed.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
}
?>
