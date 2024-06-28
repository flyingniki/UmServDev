<?php

$rsUserFields = \Bitrix\Main\UserFieldTable::getList(
    array(
        'order' => array('ENTITY_ID' => 'ASC', 'SORT' => 'ASC'),
        'filter' => array('ENTITY_ID' => 'CRM_DEAL', '>SORT' => 800),
        'select' => array('ID')
    )
);

while ($arUserField = $rsUserFields->fetch()) {
    // print_r($arUserField);
    $fieldData = \Bitrix\Main\UserFieldTable::getFieldData(
        $arUserField['ID']
    );
    // print_r($fieldData['LIST_COLUMN_LABEL']['ru']);
    $fieldsList[$fieldData['ID']] = [
        'FIELD_NAME' => $fieldData['FIELD_NAME'],
        'MULTIPLE' => $fieldData['MULTIPLE'],
        'TITLE_RU' => $fieldData['LIST_COLUMN_LABEL']['ru']
    ];
}

print_r($fieldsList);
