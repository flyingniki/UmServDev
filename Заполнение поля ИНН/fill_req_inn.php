<?php

CModule::IncludeModule('crm');

$requisite = new \Bitrix\Crm\EntityRequisite();

$rs = $requisite->getList([
    "filter" => [
        "ENTITY_ID" => '{{Компания}}', "ENTITY_TYPE_ID" => \CCrmOwnerType::Company,
    ]
]);

$reqData = $rs->fetchAll();

if ($reqData[0]['RQ_INN']) {
    $bCheckRight = false;
    $entityFields = [
        'UF_CRM_1649228940931'   => $reqData[0]['RQ_INN'],
    ];
    $entityObject = new \CCrmDeal($bCheckRight);
    $isUpdateSuccess = $entityObject->Update(
        '{{ID}}',
        $entityFields,
        $bCompare = true,
        $arOptions = []
    );
}
