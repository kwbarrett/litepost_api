<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers for JSON response and CORS
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Decode the JSON input
$data = json_decode(file_get_contents("php://input"));

// Check for JSON errors
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["valid" => false, "message" => "Invalid JSON"]);
    exit;
}

require '../db_connect.php';
$database = new Operations();
$conn = $database->dbConnection();

// Function to generate JWT
function generateJWT($user, $secret) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $user['id'],
        'email' => $user['email'],
        'exp' => time() + 3600 // Token expires in 1 hour
    ]);

    $base64UrlHeader = base64url_encode($header);
    $base64UrlPayload = base64url_encode($payload);
    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secret, true);
    $base64UrlSignature = base64url_encode($signature);

    return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Validate input fields
if (isset($data->email) && isset($data->password)) {
    $email = $data->email;
    $password = $data->password;

    // Function to validate user
    function validateUser($email, $password) {
        $db = new Operations();
        $conn = $db->dbConnection();
    
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return false;
        }
    }

    // Validate user credentials
    $user = validateUser($email, $password);
    if ($user) {
        $secret = 'QiQ1ApHsfViPFAVcbodT5gQtvNGiukEY3fFPbStPyzA='; 
        $jwt = generateJWT($user, $secret);
        echo json_encode([
            "success" => 1,
            "message" => "User logged in",
            "token" => $jwt
        ]);
    } else {
        echo json_encode([
            "success" => 0,
            "message" => "Invalid user credentials"
        ]);
    }

    // $conn->close();
} else {
    echo json_encode(["success" => 0, "message" => "Invalid input"]);
}
?>
