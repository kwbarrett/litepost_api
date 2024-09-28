<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Origin: http://localhost:4200");
// header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// header("Access-Control-Allow-Credentials: true");
// header("Content-Type: application/json; charset=UTF-8");

// if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
//     header("Access-Control-Allow-Origin: http://localhost:4200");
//     header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
//     header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
//     header("Access-Control-Allow-Credentials: true");
//     exit(0);
// }
  

error_reporting(E_ERROR);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') :
    http_response_code(405);
    echo json_encode([
        'success' => 0,
        'message' => 'Bad Reqeust Detected! Only get method is allowed',
    ]);
    exit;
endif;

require '../db_connect.php';
$database = new Operations();
$conn = $database->dbConnection();
$id = null;
$category_id = null;

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
        'options' => [
            'default' => 'all_posts',
            'min_range' => 1
        ]
    ]);
}else if( isset( $_GET['category_id'] ) ){
    $category_id = filter_var($_GET['category_id'], FILTER_VALIDATE_INT, [
        'options' => [
            'default' => 'posts_by_category',
            'min_range' => 1
        ]
    ]);
}

try {
    
    // $sql = is_numeric($id) ? " SELECT * FROM `entries` WHERE entryID='$id'" : "SELECT * FROM `entries` ORDER BY dateCreated DESC";
    if( is_numeric( $id ) ){
        $sql = "    SELECT
                        p.id,
                        p.title,
                        p.permalink,
                        p.category_id,
                        c.category_name,
                        p.postImgPath,
                        p.excerpt,
                        p.content,
                        p.isFeatured,
                        p.views,
                        p.status,
                        p.createdAt
                    FROM
                        posts p
                    INNER JOIN categories c on p.category_id = c.id
                    WHERE
                        p.id = '$id'";
    }else if( is_numeric( $category_id ) ){
        $sql = "    SELECT
                        p.id,
                        p.title,
                        p.permalink,
                        p.category_id,
                        c.category_name,
                        p.postImgPath,
                        p.excerpt,
                        p.content,
                        p.isFeatured,
                        p.views,
                        p.status,
                        p.createdAt
                    FROM
                        posts p
                    INNER JOIN categories c on p.category_id = c.id
                    WHERE
                        c.id = '$category_id'
                    ORDER BY
                        p.createdAt DESC";
    }else{
        $sql = "    SELECT
                        p.id,
                        p.title,
                        p.permalink,
                        p.category_id,
                        c.category_name,
                        p.postImgPath,
                        p.excerpt,
                        p.content,
                        p.isFeatured,
                        p.views,
                        p.status,
                        p.createdAt
                    FROM
                        posts p
                    INNER JOIN categories c on p.category_id = c.id
                    ORDER BY
                        p.createdAt DESC";
    }
    

    $stmt = $conn->prepare($sql);

    $stmt->execute();

    if ($stmt->rowCount() > 0) :

        $data = null;
        if (is_numeric($id)) {
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $formattedPost = [
                    'id' => (int) $data['id'],
                    'title' => $data['title'],
                    'permalink' => $data['permalink'],
                    'category' => [
                        'category_id' => (int) $data['category_id'],
                        'category_name' => $data['category_name']
                    ],
                    'postImgPath' => $data['postImgPath'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'isFeatured' => (bool) $data['isFeatured'],
                    'views' => (int) $data['views'],
                    'status' => $data['status'],
                    'createdAt' => $post['createdAt']
                ];
                echo json_encode(['success' => 1, 'data' => $formattedPost]);
            } else {
                echo json_encode(['success' => 0, 'error' => 'Post not found']);
            }
        } else {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        

            $formattedPosts = array_map(function($post) {
                return [
                    'id' => (int) $post['id'],
                    'title' => $post['title'],
                    'permalink' => $post['permalink'],
                    'category' => [
                        'category_id' => (int) $post['category_id'],
                        'category_name' => $post['category_name']
                    ],
                    'postImgPath' => $post['postImgPath'],
                    'excerpt' => $post['excerpt'],
                    'content' => $post['content'],
                    'isFeatured' => (bool) $post['isFeatured'],
                    'views' => (int) $post['views'],
                    'status' => $post['status'],
                    'createdAt' => $post['createdAt']
                ];
            }, $data);

            echo json_encode([
                'success' => 1,
                'data' => $formattedPosts,
            ]);

        }else :
            echo json_encode([
                'success' => 0,
                'message' => 'No Record Found!',
            ]);
        endif;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
    exit;
}