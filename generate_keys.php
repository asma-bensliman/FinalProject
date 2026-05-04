<?php
putenv('OPENSSL_CONF=C:\\wamp64\\bin\\php\\php8.0.30\\extras\\ssl\\openssl.cnf');

$config = [
    'private_key_bits' => 4096,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];
$key = openssl_pkey_new($config);

if (!$key) {
    echo "Error: " . openssl_error_string();
    exit(1);
}

openssl_pkey_export($key, $privateKey);
$details = openssl_pkey_get_details($key);
$publicKey = $details['key'];

file_put_contents('config/jwt/private.pem', $privateKey);
file_put_contents('config/jwt/public.pem', $publicKey);
echo "Keys generated successfully!";