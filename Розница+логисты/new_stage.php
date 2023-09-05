<?php

CModule::IncludeModule("crm");

$departmentId = 314;
$arSelect = ['ID', 'NAME', 'LAST_NAME'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

// поиск сделок в стадии успех
$arFilter = array(
    'STAGE_ID' => 'C12:WON',
    "CHECK_PERMISSIONS" => "N"
);
$arSelect = array();

$res = CCrmDeal::GetList(array(), $arFilter, $arSelect);
while ($row = $res->Fetch()) {
    $arContactId[] = $row['CONTACT_ID'];
}
print_r($arContactId);
if (in_array($contactId, $arContactId)) {
}
