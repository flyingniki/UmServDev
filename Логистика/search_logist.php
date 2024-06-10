<?php

CModule::IncludeModule("crm");

$departmentId = 713;
$arSelect = ['ID', 'NAME', 'LAST_NAME'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

$managerLogisticsId = CIntranetUtils::GetDepartmentManagerID($departmentId);

while ($rsEmployees = $arEmployees->fetch()) {
    if ($rsEmployees['ID'] == $managerLogisticsId) {
        continue;
    }
    $arEmp[] = 'user_' . $rsEmployees['ID'];
}

$this->SetVariable('managerLogisticsId', $arEmp);
