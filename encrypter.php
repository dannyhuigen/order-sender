<?php

//
//Function to encrypt/decrypt messages between client and server (Extra sequrity messurment)
//

function dec_enc($action, $string) {

    $data = file_get_contents((__DIR__) . "/data.json");
    $data = json_decode($data, true);

    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = $data["secret_key"];
    $secret_iv = $data["secret_iv"];

    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if( $action == 'decrypt' ){
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}