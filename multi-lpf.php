<?php
date_default_timezone_set('America/Santiago');
set_time_limit(1200);

$user = "BHP-Mel";
$pasw = "123";
include __DIR__."/login/conexion.php";

$consulta = "SELECT hash FROM masgps.hash where user='$user' and pasw='$pasw'";
$resutaldo = mysqli_query($mysqli, $consulta);
$data = mysqli_fetch_array($resutaldo);
$hash = $data['hash'];
$cap = $hash;

$qry_token = 'SELECT * FROM masgps.Token_tractec';
$resutaldo2 = mysqli_query($mysqli, $qry_token);
$data = mysqli_fetch_array($resutaldo2);
$token = $data['token'];

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/list',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{"hash":"' . $cap . '"}',
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json, text/plain, */*',
        'Content-Type: application/json',
    ),
));

$response2 = curl_exec($curl);
$json = json_decode($response2);
$array = $json->list;
curl_close($curl);

// --- Inicia procesamiento multi-curl ---
$multiCurl = curl_multi_init();
$curlArray = array();
$total = array();
$i = 0;

foreach ($array as $item) {
    $id = $item->id;
    $imei = $item->source->device_id;

    $curlArray[$i] = curl_init();
    curl_setopt_array($curlArray[$i], array(
        CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/get_state',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{"hash": "' . $cap . '", "tracker_id": ' . $id . '}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
    ));

    curl_multi_add_handle($multiCurl, $curlArray[$i]);
    $i++;
}

$running = null;
do {
    curl_multi_exec($multiCurl, $running);
    curl_multi_select($multiCurl);
} while ($running > 0);

// Procesar las respuestas
for ($i = 0; $i < count($curlArray); $i++) {
    $response2 = curl_multi_getcontent($curlArray[$i]);
    curl_multi_remove_handle($multiCurl, $curlArray[$i]);
    curl_close($curlArray[$i]);

    $json2 = json_decode($response2);

    $lat = $json2->state->gps->location->lat;
    $lng = $json2->state->gps->location->lng;
    $last_u = $json2->state->last_update;
    
    // Procesar la fecha
    $datetime = new DateTime($last_u);
    $datetime->setTimezone(new DateTimeZone('UTC'));
    $fecha_formateada = $datetime->format('Y-m-d\TH:i:s\Z');

    $plate = substr($item->label, 0, 7);
    $plate = str_replace('-', '', $plate);

    $speed = $json2->state->gps->speed;
    $direccion = $json2->state->gps->heading;
    $connection_status = $json2->state->connection_status;
    $movement_status = $json2->state->movement_status;
    $signal_level = $json2->state->gps->signal_level;
    $ignicion = $json2->state->inputs[0];
    $numero_satelites = mt_rand(10, 15);
    $hdop = number_format($numero_satelites / 16, 1);

    $motor = $ignicion ? 1 : 0;

    include __DIR__.'/driver.php';
    include __DIR__.'/giroscopio.php';

    $total[$i] = array(
        "proveedor_gps" => "Wit.la",
        "contratista" => "Tandem SA",
        "imei" => $imei,
        "patente" => $plate,
        "fecha_utc" => $fecha_formateada,
        "latitud" => number_format($lat, 6),
        "longitud" => number_format($lng, 6),
        "orientacion" => $direccion,
        "velocidad_gps" => $speed,
        "estado_motor" => $motor,
        "hdop" => $hdop,
        "codigo_evento" => 7,
        "cant_satelites" => $numero_satelites,
        "odometro_gps" => $odometerValue,
        "horometro_gps" => 0,
        "identificacion_conductor" => array(
            "nombre" => $fullName,
            "rut" => $rut,
            "codigo_ibutton" => $key_button,
            "codigo_tarjeta" => null,
            "otra_identificacion" => null
        ),
        "variables" => array(
            array("key" => "eje_x", "value" => $axisXValue),
            array("key" => "eje_y", "value" => $axisYValue),
            array("key" => "eje_z", "value" => $axisZValue)
        )
    );
}

curl_multi_close($multiCurl);
echo 
$payload = json_encode(['positions' => $total]);

//include 'envio.php';
