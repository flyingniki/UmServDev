<?php

$obEnum = new \CUserFieldEnum;
$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => [2178]));
$enum = [];
while ($arEnum = $rsEnum->Fetch()) {
    $enum[] = [
        'id' => (int)$arEnum["ID"],
        'key' => '',
        'value' => $arEnum["VALUE"]
    ];
}

$enum = json_encode($enum, JSON_UNESCAPED_UNICODE);
var_dump($enum);

$currencyList = \Bitrix\Currency\CurrencyManager::getCurrencyList();
$id = 0;
foreach ($currencyList as $key => $value) {
    $arCurrency[] = [
        'id' => $id,
        'key' => $key,
        'value' => $value
    ];
    $id++;
}
$arCurrency = json_encode($arCurrency, JSON_UNESCAPED_UNICODE);
var_dump($arCurrency);

$entityResult = \CCrmCompany::GetListEx(
    [
        'ID' => 'DESC'
    ],
    ['CHECK_PERMISSIONS' => 'N'],
    false,
    false,
    ['ID', 'TITLE'],
    []
);

while ($entity = $entityResult->fetch()) {
    // var_dump($entity);
    $requisite = new \Bitrix\Crm\EntityRequisite();
    $rs = $requisite->getList([
        "filter" => [
            "ENTITY_ID" => $entity['ID'], "ENTITY_TYPE_ID" => \CCrmOwnerType::Company,
        ]
    ]);
    $reqData = $rs->fetchAll();
    // var_dump($reqData);
    if ($reqData[0]['RQ_INN']) {
        $arAddress = Bitrix\Crm\EntityRequisite::getAddresses($reqData[0]['ID']);
        // var_dump($arAddress);
        $legalAddress = $arAddress[6];
        $address = '';
        foreach ($legalAddress as $key => $value) {
            if ($key != 'LOC_ADDR_ID' && $key != 'COUNTRY_CODE' && $value) {
                $address .= $value . ' ';
            }
        }
        $arCompanies[$entity['ID']] = [
            'id' => (int)$entity['ID'],
            'name' => $entity['TITLE'],
            'taxId' => (int)$reqData[0]['RQ_INN'],
            'legalAddress' => trim($address)
        ];
    }
}

$arCompanies = json_encode($arCompanies, JSON_UNESCAPED_UNICODE);
var_dump($arCompanies);

$by = 'id';
$order = 'ASC';
$filter = array('ACTIVE' => 'Y', 'GROUPS_ID' => array(11));
$arParams = array();

$rsUsers = CUser::GetList($by, $order, $filter, $arParams);

while ($user = $rsUsers->Fetch()) {
    // var_dump($user);
    $arUser[$user['ID']] = [
        'id' => (int)$user['ID'],
        'person' => trim($user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME']),
        'phone' => $user['WORK_PHONE'] ? (int)preg_replace('/[^0-9]/', '', (string)$user['WORK_PHONE']) : -1,
        'email' => $user['EMAIL']
    ];
}

$arUser = json_encode($arUser, JSON_UNESCAPED_UNICODE);
var_dump($arUser);

$entityResult = \CCrmCompany::GetListEx(
    [
        'ID' => 'DESC'
    ],
    ['CHECK_PERMISSIONS' => 'N'],
    false,
    false,
    ['ID', 'TITLE'],
    []
);

while ($entity = $entityResult->fetch()) {
    // var_dump($entity);
    $requisite = new \Bitrix\Crm\EntityRequisite();
    $rs = $requisite->getList([
        "filter" => [
            "ENTITY_ID" => $entity['ID'], "ENTITY_TYPE_ID" => \CCrmOwnerType::Company,
        ]
    ]);
    $reqData = $rs->fetchAll();
    // var_dump($reqData);
    if ($reqData[0]['RQ_INN']) {
        $rs = Bitrix\Crm\EntityRequisite::getAddresses($reqData[0]['ID']);
        // var_dump($arAddress);
        foreach ($rs as $res) {
            $address = '';
            foreach ($res as $key => $value) {
                if ($key != 'LOC_ADDR_ID' && $key != 'COUNTRY_CODE' && $value) {
                    $address .= $value . ' ';
                }
            }
            $resAddresses[$entity['ID']][] = $address;
        }
    }
}

$resAddresses = json_encode($resAddresses, JSON_UNESCAPED_UNICODE);
var_dump($resAddresses);

$contactResult = CCrmContact::GetListEx(
    [
        'ID' => 'DESC'
    ],
    [
        'CHECK_PERMISSIONS' => 'N',
        '!COMPANY_ID' => null,
        'HAS_PHONE' => 'Y'
    ],
    false,
    false,
    [
        'ID',
        'FULL_NAME',
        'COMPANY_ID'
    ]
);

while ($contact = $contactResult->fetch()) {
    // var_dump($contact);
    $rs = CCrmFieldMulti::GetList(
        [],
        [
            'ELEMENT_ID' => $contact['ID'],
            'VALUE_TYPE' => 'WORK'
        ]
    );
    $arContacts[$contact['COMPANY_ID']][$contact['ID']] = [
        'id' => (int)$contact['ID'],
        'person' => $contact['FULL_NAME']
    ];
    while ($ar = $rs->fetch()) {
        // var_dump($ar);
        if ($ar['TYPE_ID'] == 'PHONE') {
            $arContacts[$contact['COMPANY_ID']][$contact['ID']]['phone'] = preg_replace('/[^0-9]/', '', (string)$ar['VALUE']) ?? -1;
        }
        if ($ar['TYPE_ID'] == 'EMAIL') {
            $arContacts[$contact['COMPANY_ID']][$contact['ID']]['email'] = (string)$ar['VALUE'] ?? '';
        }
    }
}

$arContacts = json_encode($arContacts, JSON_UNESCAPED_UNICODE);
var_dump($arContacts);
