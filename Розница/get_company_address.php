<?php

CModule::IncludeModule('crm');

/**
 * get EntityRequisite & set deliveryAddress
 */

$requisite = new \Bitrix\Crm\EntityRequisite();
$rs = $requisite->getList([
    'filter' => [
        'ENTITY_ID' => '{{Компания}}', 'ENTITY_TYPE_ID' => 4,
    ]
]);

$reqData = $rs->fetchAll();

if (isset($reqData[0]['ID'])) {
    $arAddress = \Bitrix\Crm\EntityRequisite::getAddresses($reqData[0]['ID']);
    $deliveryAddress = $arAddress[11];
    if ($deliveryAddress) {
        $setAddress = "{$deliveryAddress['CITY']} {$deliveryAddress['ADDRESS_1']} {$deliveryAddress['ADDRESS_2']}";
        $this->SetVariable('deliveryAddress', $setAddress);
    } else {
        $this->SetVariable('deliveryAddress', 'В компании не заполнен адрес доставки');
    }
} else {
    $this->SetVariable('deliveryAddress', 'Нет реквизитов для данной компании');
}

/**
 * get company's binded contacts and check UF_CRM_1706614570 field
 */

$contactResult = CCrmContact::GetListEx(
    [],
    [
        'ID' => '{=A74394_43123_60785_72500:Value}',
        'UF_CRM_1706614570' => 1957,
        'CHECK_PERMISSIONS' => 'N'
    ],
    false,
    false,
    [
        'ID',
        'UF_CRM_1706614570'
    ]
);

if ($contact = $contactResult->fetch()) {
    $contactID = $contact['ID'];
    $this->SetVariable('contactID', $contactID);
}
