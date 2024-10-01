<?php

$curl = curl_init();

// Set API endpoint URL
$url = 'http://www.trackermasgps.com/api-v2/tracker/employee/read';

// Set authentication details (replace with your actual values)
$hash =$cap;//'682f6173eb989ce20ab5e444b8520ae8';
$tracker_id =$id; //10302375;

// Create the request body as a JSON string
$requestBody = json_encode(['hash' => $hash, 'tracker_id' => $tracker_id]);

curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_POSTFIELDS => $requestBody,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
));

// Execute the cURL request and handle potential errors

$response = curl_exec($curl);
$curlError = curl_error($curl);

if ($curlError) {
    echo "Error al obtener datos de la API: " . $curlError . PHP_EOL;
} else {
    // Decodificar la respuesta JSON
    $responseData = json_decode($response, true);

    if (isset($responseData['current'])) {
        $firstName = $responseData['current']['first_name'] ?? '';
        $lastName = $responseData['current']['last_name'] ?? '';
        $fullName = trim($firstName . ' ' . $lastName);
        
        $key_button=$responseData['current']['hardware_key']?? '';
        $key_button=substr($key_button,4);
        
        $rut=$responseData['current']['personnel_number']?? '';


        if (empty($fullName)) {
              $fullName= "No Asignado";
        } else {
            $fullName;
        }
    } else {
        $fullName= "No Asignado" ;
        $key_button="ABCDEF123456";
        $rut="26694722-4";

    }
}

curl_close($curl);
