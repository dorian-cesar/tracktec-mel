<?php

$curl22 = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://dbms-qa.tracktec.cl/v1/data/bd4fa98c-b4ad-4934-92c5-6e328383df4e/insert',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
"positions": [
{
"proveedor_gps": "TRACKTEC",
"contratista": "Transportes XYZ",
"imei": "860789456123123",
"patente": "TRTC24",
"fecha_utc": "2024-05-24T20:43:50Z",
"latitud": -30.131654,
"longitud": -70.126455,
"orientacion": 240,
"velocidad_gps": 10,
"estado_motor": 1,
"hdop": 0.3,
"codigo_evento": 105,
"cant_satelites": 8,
"odometro_gps": 350000.999,
"horometro_gps": 0,
"identificacion_conductor": {
"nombre": "Julio Fica",
"rut": "15107676-9",
"codigo_ibutton": "00001234A234",
"codigo_tarjeta": null,
"otra_identificacion": null
},
"variables": [
{
"key": "eje_x",
"value": 3.3
},
{
"key": "eje_y",
"value": 0.3
},
{
"key": "eje_z",
"value": 0.1
}
]
}
]
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: ••••••'
  ),
));

$response = curl_exec($curl22);

curl_close($curl22);
echo $response;
