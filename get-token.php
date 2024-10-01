<?php


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
  CURLOPT_POSTFIELDS =>'{
"username": "dbms_wit",
"password": "44C50782BA5CC82006A4CD173A63CFC6"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));


$response = curl_exec($curl);



curl_close($curl);







// Decodificamos la respuesta JSON en un array asociativo
$datos = json_decode($response, true);

// Verificamos si la decodificación fue exitosa
if (json_last_error() === JSON_ERROR_NONE) {
    // Extraemos el token del array
    $token = $datos['data']['token'];

    // Ahora puedes utilizar el token como necesites
    echo "El token es: " . $token;
} else {
    echo "Error al decodificar la respuesta JSON.";
}
