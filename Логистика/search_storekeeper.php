<?php

CModule::IncludeModule("crm");

$departmentId = 712;
$arSelect = ['ID', 'NAME', 'LAST_NAME'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

$managerLogisticsId = CIntranetUtils::GetDepartmentManagerID($departmentId);

$arStorekeeper[] = 'user_' . $managerLogisticsId;

$this->SetVariable('arStorekeeper', $arStorekeeper);
