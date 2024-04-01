<?php

CModule::IncludeModule('crm');
CModule::IncludeModule('im');

$date = ConvertDateTime('{{Дата поступления от поставщика}}', 'YYYY-MM-DD', 'ru');

$url = "https://www.quickrun.ru/api/1.0/client/orders/{$date}";

$headers = ['Authorization: a:38:3OthPkVi'];

$dimensions = '{{Габариты(формат: д*ш*в)}}';

$post_data = [
    'timeFrom' => '9:00',
    'timeTo' => '18:00',
    'address' => '{{Адрес забора у поставщика}}',
    'buyerName' => '{{Контакт забора у поставщика (текст)}}',
    'goods' => '{{Название}}',
    'number' => 'Заявка логисту: {{ID}}',
    'additionalInfo' => 'Номер заказа поставщика: {{Заказ поставщика(номер)}} Габариты: {$dimensions}',
    'price' => '{{Сумма}}',
    'phone' => '{=A85941_66635_47666_80463:PHONE}',
    'dimensions' => [
        'weight' => '{{Вес}}',
    ]
];

$data_json = json_encode($post_data);

$curl = curl_init();

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);

$result = curl_exec($curl);

curl_close($curl);

$result = json_decode($result, true);

$user_id = intval(mb_substr('{{Ответственный}}', 5));

if ($result['success']) {
    $message = 'Заявка на стадии {{Стадия сделки (текст)}} в приложении Бегунок успешно создана';
    $arFields = array(
        'MESSAGE' => $message,
        'MESSAGE_TYPE' => 'S',
        'TO_USER_ID' => $user_id,
    );
} else {
    $message = $result['error'];
    $arFields = array(
        'MESSAGE' => $message,
        'MESSAGE_TYPE' => 'S',
        'TO_USER_ID' => $user_id,
    );
}
CIMMessenger::Add($arFields);

$this->SetVariable('message', $message);
