<?php

CModule::IncludeModule('crm');
CModule::IncludeModule('im');

$phonesToCheck = array();
$emailsToCheck = array();

$contact_id = '{{ID}}';
$created_by = mb_substr('{{Ответственный}}', 5);

$res = CCrmFieldMulti::GetListEx(
    array('ELEMENT_ID' => 'asc'),
    array(
        'CHECK_PERMISSIONS' => 'N',
        'ENTITY_ID' => 'CONTACT',
        'ELEMENT_ID' => $contact_id,
        'TYPE_ID' => array('PHONE', 'EMAIL')
    )
);

while ($row = $res->Fetch()) {
    switch ($row['TYPE_ID']) {
        case 'PHONE':
            if (strlen($row['VALUE']) > 10) {
                if (substr($row['VALUE'], 0, 1) == 8) {
                    $tel_clean = '7' . substr(preg_replace('|[^0-9]*|', '', $row['VALUE']), 1);
                } else {
                    $tel_clean = preg_replace('|[^0-9]*|', '', $row['VALUE']);
                }
                array_push($phonesToCheck, $tel_clean);
            }
            break;
        case 'EMAIL':
            array_push($emailsToCheck, $row['VALUE']);
            break;
    }
}

$duplicateIds = array();

// Поиск дублей в базе по номеру телефона

$dbPhones = CCrmFieldMulti::GetListEx(
    array('ELEMENT_ID' => 'asc'),
    array(
        'CHECK_PERMISSIONS' => 'N',
        'ENTITY_ID' => 'CONTACT',
        '!ELEMENT_ID' => $contact_id,
        'TYPE_ID' => 'PHONE',
    )
);

while ($arDouble = $dbPhones->Fetch()) {
    if (strlen($arDouble['VALUE']) > 10) {
        if (substr($arDouble['VALUE'], 0, 1) == 8) {
            $tel_clean = '7' . substr(preg_replace('|[^0-9]*|', '', $arDouble['VALUE']), 1);
        } else {
            $tel_clean = preg_replace('|[^0-9]*|', '', $arDouble['VALUE']);
        }

        if (in_array($tel_clean, $phonesToCheck) && !in_array($arDouble['ELEMENT_ID'], $duplicateIds)) {
            array_push($duplicateIds, $arDouble['ELEMENT_ID']);
        }
    }
}

// Поиск дублей в базе по email

if (!empty($emailsToCheck)) {
    $dbEmails = CCrmFieldMulti::GetListEx(
        array('ELEMENT_ID' => 'asc'),
        array(
            'CHECK_PERMISSIONS' => 'N',
            'ENTITY_ID' => 'CONTACT',
            '!ELEMENT_ID' => $contact_id,
            'TYPE_ID' => 'EMAIL',
            'VALUE' => $emailsToCheck
        )
    );

    while ($arDoubleEmails = $dbEmails->Fetch()) {
        if (!in_array($arDoubleEmails['ELEMENT_ID'], $duplicateIds)) {
            array_push($duplicateIds, $arDoubleEmails['ELEMENT_ID']);
        }
    }
}

$message = '';

foreach ($duplicateIds as $key => $id) {
    if ($key != array_key_last($duplicateIds)) {
        $message .= "[url=https://crm.umserv.ru/crm/contact/details/{$id}/]{$id}[/url]; ";
    } else {
        $message .= "[url=https://crm.umserv.ru/crm/contact/details/{$id}/]{$id}[/url]";
    }
}

// Информировать пользователя что созданный контакт является дублем и удалить контакт.
if (count($duplicateIds) > 0) {
    $arFields = array(
        "MESSAGE" => "Контакт с таким телефоном и e-mail уже существует. Существующие контакты: {$message}. Созданный Вами контакт сейчас будет перемещен в [url=https://crm.umserv.ru/crm/recyclebin/]корзину[/url]!",
        "MESSAGE_TYPE" => 'S',
        "TO_USER_ID" => $created_by,
    );
    CIMMessenger::Add($arFields);
}
