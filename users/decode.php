<?php

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function decodeJWT($jwt, $secret) {
    list($header, $payload, $signature) = explode('.', $jwt);

    $decoded_header = json_decode(base64url_decode($header), true);
    $decoded_payload = json_decode(base64url_decode($payload), true);

    $valid_signature = hash_hmac('sha256', "$header.$payload", $secret, true);
    $valid_signature = base64url_encode($valid_signature);

    if ($signature === $valid_signature) {
        return $decoded_payload;
    } else {
        return false;
    }
}

// Example usage
$jwt = "your.jwt.token.here";
$secret = "your-256-bit-secret";
$decoded_payload = decodeJWT($jwt, $secret);

if ($decoded_payload) {
    echo "JWT is valid. Payload: ";
    print_r($decoded_payload);
} else {
    echo "Invalid JWT.";
}
