<?php

CModule::IncludeModule("crm");

// delete without taking into account SEARCH_CONTENT field
$contactResult = CCrmContact::GetListEx(
    [
        'ID' => 'DESC'
    ],
    [
        'CHECK_PERMISSIONS' => 'N',
        'HAS_PHONE' => 'Y',
        'ID' => 4030
    ],
    false,
    false,
    []
);

while ($contact = $contactResult->fetch()) {
    $rsMulti = CCrmFieldMulti::GetList(
        [],
        [
            'ENTITY_ID' => 'CONTACT',
            'ELEMENT_ID' => $contact['ID']
        ]
    );
    while ($arMulti = $rsMulti->fetch()) {
        $multi = new \CCrmFieldMulti();
        $clearResult = $multi->Delete($arMulti['ID'], $options = null);
        if ($clearResult === false) {
            echo 'Incorrect parameters';
        } else {
            var_dump($clearResult->AffectedRowsCount());
        }
    }
}

// delete with taking into account SEARCH_CONTENT field
$contactId = 2421;

$rsMulti = CCrmFieldMulti::GetList(
    [],
    [
        'ENTITY_ID' => 'CONTACT',
        'ELEMENT_ID' => $contactId
    ]
);

while ($arMulti = $rsMulti->fetch()) {
    if ($arMulti['TYPE_ID'] == 'PHONE') {
        $arPhoneValues[] = $arMulti['VALUE'];
    }
}

foreach ($arPhoneValues as $value) {
    $value = substr(preg_replace('/[^0-9]/', '', $value), 1);
    $dbDel = $DB->query(
        "UPDATE b_crm_deal 
        SET SEARCH_CONTENT = REPLACE(SEARCH_CONTENT, $value, 'X') 
        WHERE SEARCH_CONTENT LIKE '%$value%'"
    );
}
