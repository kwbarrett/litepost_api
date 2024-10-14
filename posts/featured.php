<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

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
}

try {
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
                    p.isFeatured = true
                LIMIT 4";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0) :
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if( $data ){
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
        }else{
            echo json_encode(['success' => 0, 'error' => 'Post not found']);
        }
    endif;   
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => 0,
        'message' => $e->getMessage()
    ]);
    exit;
}