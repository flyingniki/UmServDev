<?php

CModule::IncludeModule('im');
CModule::IncludeModule("crm");

$date = ConvertDateTime('{{Дата и время забора}}', "YYYY-MM-DD", "ru");

$account_number = '{{Номер счета}}';

$url = "https://www.quickrun.ru/api/1.0/client/orders/{$date}";

$headers = ['Authorization: a:38:3OthPkVi'];

$dimensions = '{{Габариты}}';

switch ($separator) {
    case '*':
        $dimensions = explode($separator, $dimensions); // разделитель на русском языке
        break;
        // en
    case 'X':
        $dimensions = explode($separator, $dimensions); // разделитель на русском языке
        break;
    case 'x':
        $dimensions = explode($separator, $dimensions); // разделитель на русском языке
        break;
        // ru
    case 'Х':
        $dimensions = explode($separator, $dimensions); // разделитель на русском языке
        break;
    case 'х':
        $dimensions = explode($separator, $dimensions); // разделитель на русском языке
        break;
    default:
        $dimensions = '';
        break;
}

$post_data = [
    // sender
    [
        "timeFrom" => "9:00",

        "timeTo" => "18:00",

        "address" => "{{Адрес отправителя}}",

        "buyerName" => "{{ФИО отправителя}}",

        "goods" => $account_number,

        "number" => "отправитель-{{ID}}",

        "additionalInfo" => "Это карточка отправителя. {{Комментарий к забору}}",

        "price" => "",

        "phone" => "{{Телефон отправителя}}",

        "dimensions" => [

            "weight" => "{{Вес}}",

            "height" => $dimensions[2] ? $dimensions[2] : '',

            "length" => $dimensions[0] ? $dimensions[0] : '',

            "width" => $dimensions[1] ? $dimensions[1] : ''

        ]
    ],
    // reciever
    [
        "timeFrom" => "9:00",

        "timeTo" => "18:00",

        "address" => "{{Адрес получателя}}",

        "buyerName" => "{{ФИО получателя}}",

        "goods" => $account_number,

        "number" => "получатель-{{ID}}",

        "additionalInfo" => "Это карточка получателя. {{Комментарий к забору}}",

        "price" => "",

        "phone" => "{{Телефон получателя}}",

        "dimensions" => [

            "weight" => "{{Вес}}",

            "height" => $dimensions[2] ? $dimensions[2] : '',

            "length" => $dimensions[0] ? $dimensions[0] : '',

            "width" => $dimensions[1] ? $dimensions[1] : ''

        ]
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
    $message = "Заявка в приложении Бегунок успешно создана";
    $arFields = array(
        "MESSAGE" => $message,
        "MESSAGE_TYPE" => 'S',
        "TO_USER_ID" => $user_id,
    );
} else {
    $message = $result['error'];
    $arFields = array(
        "MESSAGE" => $message,
        "MESSAGE_TYPE" => 'S',
        "TO_USER_ID" => $user_id,
    );
}
CIMMessenger::Add($arFields);

$this->SetVariable('message', $message);
