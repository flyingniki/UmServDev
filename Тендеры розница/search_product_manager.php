<?php

CModule::IncludeModule("crm");

$departmentId = 729; // продакт-менеджеры
$arSelect = ['ID', 'NAME', 'LAST_NAME'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

$managerProductId = CIntranetUtils::GetDepartmentManagerID($departmentId);

while ($rsEmployees = $arEmployees->fetch()) {
    if ($rsEmployees['ID'] == $managerProductId || $rsEmployees['ID'] == 214) { //искл. Р. Фяйзуллин
        continue;
    }
    $arEmp[] = 'user_' . $rsEmployees['ID'];
}

$managerProductId = 'user_' . $managerProductId;

$this->SetVariable('arEmp', $arEmp);
$this->SetVariable('managerProductId', $managerProductId);
