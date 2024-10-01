<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://dbms-qa.tracktec.cl/v1/data/bd4fa98c-b4ad-4934-92c5-6e328383df4e/insert',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$payload,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJkYm1zX3dpdCIsImlhdCI6MTcyNzcyOTI1OSwianRpIjoiYmQ0ZmE5OGMtYjRhZC00OTM0LTkyYzUtNmUzMjgzODNkZjRlIiwiZXhwIjoxNzI3ODE1NjU5fQ.q5XoZePyJ4OXABkYqq8uKa_YRNL2gjsGI-YST7qkUkXvve5owV_sF2LvtFVBtjG3EMbvpn1JJzQnXhPO9yPUyQ'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

