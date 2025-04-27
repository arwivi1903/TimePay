<?php
define('ENCRYPTION_KEY', 'your-secret-key'); 
define('ENCRYPTION_IV', '1234567891011121'); 
define('ENCRYPTION_METHOD', 'AES-256-CBC'); 

function encryptData($data) {
    return base64_encode(openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, ENCRYPTION_IV));
}

function decryptData($encryptedData) {
    return openssl_decrypt(base64_decode($encryptedData), ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, ENCRYPTION_IV);
}
?>