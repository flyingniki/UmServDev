<?php

/*
    [ID] => 218254
    [ENTITY_TYPE] => LEAD
    [ENTITY_ID] => 10890
    [ENTITY_FIELD] => ACTIVITIES
    [DATE_CREATE] => 25.09.2023 16:50:41
    [EVENT_ID] => 216340
    [EVENT_NAME] => Создан звонок
    [EVENT_TYPE] => 1
    [EVENT_TEXT_1] => Тема: Входящий от 7 861 992-47-08

    [EVENT_TEXT_2] => 
    [FILES] => 
    [CREATED_BY_ID] => 313
    [CREATED_BY_LOGIN] => rh@umserv.ru
    [CREATED_BY_NAME] => Рамиль
    [CREATED_BY_LAST_NAME] => Хабибулин
    [CREATED_BY_SECOND_NAME] =>
*/

CModule::IncludeModule('crm');

$dealId = 4074;

$params = [
    'TYPE_ID' => CCrmActivityType::Call,
    'BINDINGS' => [
        ['OWNER_ID' => $dealId, 'OWNER_TYPE_ID' => CCrmOwnerType::Deal]
    ],
    'SUBJECT' => 'Outgoing call',
    'COMPLETED' => 'Y',
    'DESCRIPTION' => 'Some description',
    'RESPONSIBLE_ID' => 319,
    'DIRECTION' => CCrmActivityDirection::Incoming
];

$response = CCrmActivity::Add($params, false, false);
