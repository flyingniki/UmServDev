<?php

CModule::IncludeModule("crm");

$departmentId = 712;
$arSelect = ['ID', 'NAME', 'LAST_NAME'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

$managerLogisticsId = CIntranetUtils::GetDepartmentManagerID($departmentId);

while ($rsEmployees = $arEmployees->fetch()) {
    if (
        ($rsEmployees['ID'] == $managerLogisticsId)
        || ($rsEmployees['ID'] == 40)
        || ($rsEmployees['ID'] == 195)
        || ($rsEmployees['ID'] == 318)
        || ($rsEmployees['ID'] == 524)
    ) { // ID Яровой, Зуев, Павлов, Куликов
        continue;
    }
    $arStorekeeper[] = 'user_' . $rsEmployees['ID'];
}

// check user groups
$arGroups = CUser::GetUserGroup(mb_substr('{{МПП}}', 5));

if (in_array(36, $arGroups)) { //corp
    $arStorekeeper = ['user_318']; //Павлов
} elseif (in_array(37, $arGroups)) { //retail
    $arStorekeeper = ['user_326']; //Тимофеев
}

$this->SetVariable('arStorekeeper', $arStorekeeper);
