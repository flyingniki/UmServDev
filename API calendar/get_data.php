<?php

$urlCalendar = 'https://production-calendar.ru/';

$paramCalendar = http_build_query(array(
    'country' => '',
    'period' => '',
    'format' => ''
));
$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HEADER => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $urlCalendar,
    CURLOPT_POSTFIELDS => $paramCalendar,
));
$data = curl_exec($ch);
$data = json_decode($data, true)['result'];
curl_close($ch);

var_dump($data);
