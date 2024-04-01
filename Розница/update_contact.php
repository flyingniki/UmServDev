<?php

CModule::IncludeModule('crm');

$bCheckRight = false;
$contactId = 123;
$contactFields = [
    'UF_CRM_1706614570' => 1957
];

$contactEntity = new \CCrmContact($bCheckRight);

$isUpdateSuccess = $contactEntity->Update(
    $contactId,
    $contactFields,
    $bCompare = true,
    $arOptions = [
        'CURRENT_USER' => \CCrmSecurityHelper::GetCurrentUserID(),
        'IS_SYSTEM_ACTION' => true,
        `ENABLE_DUP_INDEX_INVALIDATION` => true,
        'REGISTER_SONET_EVENT' => false,
        'DISABLE_USER_FIELD_CHECK' => true,
        'DISABLE_REQUIRED_USER_FIELD_CHECK' => true,
    ]
);
