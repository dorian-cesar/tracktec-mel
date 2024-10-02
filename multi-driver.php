<?php
date_default_timezone_set('America/Santiago');
set_time_limit(1200);

$user = "BHP-Mel";
$pasw = "123";
include "login/conexion.php";

$consulta = "SELECT hash FROM masgps.hash where user='$user' and pasw='$pasw'";
$resultado = mysqli_query($mysqli, $consulta);
$data = mysqli_fetch_array($resultado);
$hash = $data['hash'];

$qry_token = 'SELECT * FROM masgps.Token_tractec';
$resultado2 = mysqli_query($mysqli, $qry_token);
$data2 = mysqli_fetch_array($resultado2);
$token = $data2['token'];

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/list',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '{"hash":"' . $hash . '"}',
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json, text/plain, */*',
        'Content-Type: application/json',
    ),
));
$response = curl_exec($curl);
curl_close($curl);

$json = json_decode($response);
$array = $json->list;

// Función para procesar lotes de 10 solicitudes
function processBatch($batch, $hash) {
    $mh = curl_multi_init();
    $curl_array = [];

    foreach ($batch as $item) {
        $id = $item->id;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/tracker/get_state',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{"hash": "' . $hash . '", "tracker_id": ' . $id . '}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        curl_multi_add_handle($mh, $curl);
        $curl_array[] = $curl;
    }

    // Ejecutar todas las solicitudes en paralelo
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    // Obtener y cerrar los manejadores de las solicitudes
    $responses = [];
    foreach ($curl_array as $ch) {
        $response = curl_multi_getcontent($ch);
        $responses[] = json_decode($response);
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);

    return $responses;
}

$total = [];
$batchSize = 10;
$batch = [];
$i = 0;

foreach ($array as $item) {
    $batch[] = $item;
    
    // Si tenemos un lote de 10 o hemos llegado al final del array, lo procesamos
    if (count($batch) == $batchSize || $i == count($array) - 1) {
        $responses = processBatch($batch, $hash);

        foreach ($responses as $json2) {
            $lat = $json2->state->gps->location->lat;
            $lng = $json2->state->gps->location->lng;
            $last_u = $json2->state->last_update;
            $datetime = new DateTime($last_u);
            $datetime->setTimezone(new DateTimeZone('UTC'));
            $fecha_formateada = $datetime->format('Y-m-d\TH:i:s\Z');

            $plate = substr($item->label, 0, 7);
            $plate = str_replace('-', '', $plate);

            $speed = $json2->state->gps->speed;
            $direccion = $json2->state->gps->heading;
            $ignicion = $json2->state->inputs[0];
            $motor = $ignicion ? 1 : 0;

            include 'driver.php';
            include 'giroscopio.php';

            $json = array(
                "proveedor_gps" => "Wit.la",
                "contratista" => "Tandem SA",
                "imei" => $item->source->device_id,
                "patente" => $plate,
                "fecha_utc" => $fecha_formateada,
                "latitud" => number_format($lat, 6),
                "longitud" => number_format($lng, 6),
                "orientacion" => $direccion,
                "velocidad_gps" => $speed,
                "estado_motor" => $motor,
                "hdop" => 0.8, // Ejemplo
                "codigo_evento" => 7,
                "cant_satelites" => 12,
                "odometro_gps" => $odometerValue,
                "horometro_gps" => 0,
                "identificacion_conductor" => array(
                    "nombre" => $fullName,
                    "rut" => $rut,
                    "codigo_ibutton" => $key_button,
                ),
                "variables" => array(
                    array("key" => "eje_x", "value" => $axisXValue),
                    array("key" => "eje_y", "value" => $axisYValue),
                    array("key" => "eje_z", "value" => $axisZValue),
                )
            );

            $total[] = $json;
        }

        // Vaciar el lote
        $batch = [];
    }

    $i++;
}

// Enviar datos procesados
echo
$payload = json_encode(['positions' => $total]);
//include 'envio.php';
?>
