<?php
$multiCurl = curl_multi_init(); // Inicializa multi-cURL
$curlArray = []; // Almacena cada handle individual
$responses = []; // Almacena las respuestas de cada petición

// Suponiendo que tenemos un array de tracker_ids para múltiples solicitudes
$tracker_ids = [$id1, $id2, $id3, ...]; // Reemplaza con tus IDs reales

foreach ($tracker_ids as $tracker_id) {
    $curl = curl_init();

    // Set API endpoint URL
    $url = 'http://www.trackermasgps.com/api-v2/tracker/employee/read';

    // Set authentication details
    $hash = $cap;
    
    // Create the request body as a JSON string
    $requestBody = json_encode(['hash' => $hash, 'tracker_id' => $tracker_id]);

    // Configurar cada cURL individualmente
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST', // Debe ser POST en lugar de GET ya que envías un body
        CURLOPT_POSTFIELDS => $requestBody,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    // Añadir cada handle de cURL al manejador multi-cURL
    curl_multi_add_handle($multiCurl, $curl);

    // Almacenar el handle y asociarlo al tracker_id
    $curlArray[$tracker_id] = $curl;
}

// Ejecutar todas las solicitudes simultáneamente
$running = null;
do {
    curl_multi_exec($multiCurl, $running);
    curl_multi_select($multiCurl); // Espera si no hay actividad inmediata
} while ($running > 0);

// Recoger las respuestas de cada cURL
foreach ($curlArray as $tracker_id => $curl) {
    $response = curl_multi_getcontent($curl);
    $curlError = curl_error($curl);

    if ($curlError) {
        echo "Error al obtener datos de la API para Tracker ID $tracker_id: " . $curlError . PHP_EOL;
    } else {
        // Decodificar la respuesta JSON
        $responseData = json_decode($response, true);

        if (isset($responseData['current'])) {
            $firstName = $responseData['current']['first_name'] ?? '';
            $lastName = $responseData['current']['last_name'] ?? '';
            $fullName = trim($firstName . ' ' . $lastName);
            
            $key_button = $responseData['current']['hardware_key'] ?? '';
            $key_button = substr($key_button, 4);
            
            $rut = $responseData['current']['personnel_number'] ?? '';

            if (empty($fullName)) {
                $fullName = "No Asignado";
            }
        } else {
            $fullName = "No Asignado";
            $key_button = "ABCDEF123456";
            $rut = "26694722-4";
        }

        // Almacenar la respuesta procesada
        $responses[$tracker_id] = [
            'fullName' => $fullName,
            'key_button' => $key_button,
            'rut' => $rut
        ];
    }

    // Cerrar el handle cURL individual
    curl_multi_remove_handle($multiCurl, $curl);
    curl_close($curl);
}

// Cerrar el manejador multi-cURL
curl_multi_close($multiCurl);

// Las respuestas procesadas están en $responses
