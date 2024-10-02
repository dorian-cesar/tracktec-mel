<?php
date_default_timezone_set('America/Santiago');

set_time_limit(1200);

$user = "BHP-Mel";


$pasw = "123";

include "login/conexion.php";

Loop:

$consulta = "SELECT hash FROM masgps.hash where user='$user' and pasw='$pasw'";

$resutaldo = mysqli_query($mysqli, $consulta);

$data = mysqli_fetch_array($resutaldo);

$hash = $data['hash'];

$cap = $hash;


//include 'get-token.php';


$qry_token='SELECT * FROM masgps.Token_tractec ';

$resutaldo2 = mysqli_query($mysqli, $qry_token);

$data = mysqli_fetch_array($resutaldo2);


$token = $data['token']; 

//$token='eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJkYm1zX3dpdCIsImlhdCI6MTcyNzcyOTI1OSwianRpIjoiYmQ0ZmE5OGMtYjRhZC00OTM0LTkyYzUtNmUzMjgzODNkZjRlIiwiZXhwIjoxNzI3ODE1NjU5fQ.q5XoZePyJ4OXABkYqq8uKa_YRNL2gjsGI-YST7qkUkXvve5owV_sF2LvtFVBtjG3EMbvpn1JJzQnXhPO9yPUyQ';

//header("refresh:2");
$listado = '';
$i = 0;
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
    'Accept-Language: es-419,es;q=0.9,en;q=0.8',
    'Connection: keep-alive',
    'Content-Type: application/json',
    'Cookie: _ga=GA1.2.728367267.1665672802; locale=es; _gid=GA1.2.967319985.1673009696; _gat=1; session_key=5d7875e2bf96b5966225688ddea8f098',
    'Origin: http://www.trackermasgps.com',
    'Referer: http://www.trackermasgps.com/',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36'
  ),
));

$response2 = curl_exec($curl);

$json = json_decode($response2);

$array = $json->list;


//echo '[';
foreach ($array as $item) {


  $id = $item->id;
  $imei = $item->source->device_id;
  //echo " , &nbsp";


  $curl = curl_init();

  curl_setopt_array($curl, array(
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
      'Content-Type: application/json'
    ),
  ));


  $response2 = curl_exec($curl);

  curl_close($curl);

  $json2 = json_decode($response2);
  //$.state.gps.location.lat


  $lat = $array2 = $json2->state->gps->location->lat;


  $lng = $array2 = $json2->state->gps->location->lng;


  $last_u = $array2 = $json2->state->last_update;

  // Fecha y hora original
$fecha_original = $last_u;

// Creamos un objeto DateTime a partir de la fecha original
$datetime = new DateTime($fecha_original);

// Establecemos la zona horaria a UTC (Z)
$datetime->setTimezone(new DateTimeZone('UTC'));

// Formateamos la fecha y hora según el formato deseado
$fecha_formateada = $datetime->format('Y-m-d\TH:i:s\Z');

//echo $fecha_formateada; // Imprimirá: 2024-10-01T15:08:51Z

  $ultima_Conexion = date("d/m/Y H:i:s", strtotime($last_u));


  $plate = substr($item->label, 0, 7);

  $plate=str_replace('-','',$plate);

  $speed = $json2->state->gps->speed;

  $direccion = $json2->state->gps->heading;

  $connection_status = $json2->state->connection_status;

  $movement_status = $json2->state->movement_status;

  $signal_level = $json2->state->gps->signal_level;

  $ignicion = $json2->state->inputs[0];

  $numero_satelites= mt_rand(10,15);

  $hdop=$numero_satelites/16;
  $hdop=number_format($hdop,1);

  if ($ignicion) {
    $motor = 1;
  } else {
    $motor = 0;
  }

 // include 'odometro.php';



  include 'driver.php';

  include 'giroscopio.php';

  
    $json =array(


    
     
    
      "proveedor_gps"=> "Wit.la",
      "contratista"=> "Tandem SA",
      "imei"=> $imei,
      "patente"=> $plate,
      "fecha_utc"=> $fecha_formateada,
      "latitud"=> number_format($lat,6),
      "longitud"=>  number_format($lng,6),
      "orientacion"=> $direccion,
      "velocidad_gps"=> $speed,
      "estado_motor"=> $motor,
      "hdop"=>$hdop ,
      "codigo_evento"=> 7,
      "cant_satelites"=> $numero_satelites,
      "odometro_gps"=>$odometerValue,
      "horometro_gps"=> 0,
      "identificacion_conductor"=> 
        [
          "nombre"=> $fullName,
      "rut"=> $rut,
      "codigo_ibutton"=> $key_button,
      "codigo_tarjeta"=> null,
      "otra_identificacion"=> null
        ],
  
        "variables"=> [
  [
  "key"=> "eje_x",
  "value"=> $axisXValue
  ],
  [
  "key"=> "eje_y",
  "value"=> $axisYValue
  ],
  [
  "key"=> "eje_z",
  "value"=> $axisZValue
  ]
  ]
        );
      

  $total[$i] = $json;
 

  $i++;
 



}
 
Ruta:

$payload= json_encode(['positions'=>$total]);



include 'envio.php';

goto Loop;



