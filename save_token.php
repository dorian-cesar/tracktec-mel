<?php
date_default_timezone_set('America/Santiago');

// Database connection details (replace with your actual credentials)
include 'login/conexion.php';

// Initialize cURL
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://dbms-qa.tracktec.cl/v1/authentication/login',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => '{
    "username": "dbms_wit",
    "password": "44C50782BA5CC82006A4CD173A63CFC6"
  }',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

// Execute cURL and retrieve response
echo 
$response = curl_exec($curl);

// Close cURL connection
curl_close($curl);

// Check for cURL errors
if (curl_errno($curl)) {
  echo 'cURL error: ' . curl_error($curl);
  die();
}

// Decode JSON response (ensure proper error handling)
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
  echo 'JSON decode error: ' . json_last_error_msg();
  die();
}

// Extract token from response (assuming 'token' key exists)
if (!isset($data['data']['token'])) {
   'Missing token in response';
  die();
}
$token = $data['data']['token'];

// Prepare SQL statement (prevent SQL injection)
$sql = "UPDATE Token_tractec SET token = $token WHERE cliente = mel";

$resutaldo = mysqli_query($mysqli, $sql);

//UPDATE `masgps`.`Token_tractec` SET `token` = 'eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJkYm1zX3dpdCIsImlhdCI6MTcyNzgxMjMwOCwianRpIjoiYmQ0ZmE5OGMtYjRhZC00OTM0LTkyYzUtNmUzMjgzODNkZjRlIiwiZXhwIjoxNzI3ODk4NzA4fQ.fYVZebPRnVTlqb1G3-ROolhhOnCqIpYDOgZBrXnZeiAGOg0Bs1ayQwQoaofxY4T-F48dY-tyYlM_ZCb5js_MiQ' WHERE (`id` = '1');



