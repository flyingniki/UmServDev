<?php

CModule::IncludeModule('crm');

$lead = CCrmLead::GetByID('{{ID}}');

$departmentId = 720; // МПП
$arSelect = ['ID'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

$departmentId = 314; // РОП
$managerId = CIntranetUtils::GetDepartmentManagerID($departmentId);

while ($rsEmployees = $arEmployees->fetch()) {
    $arEmp[] = $rsEmployees['ID'];
}

$arEmp[] = $managerId;

if (in_array($lead['SOURCE_ID'], [1, 2, 6, 'CALL']) && in_array($lead['ASSIGNED_BY_ID'], $arEmp)) {
    $dealAssigned = 'user_' . $lead['ASSIGNED_BY_ID'];
}

$this->SetVariable('source_id', $lead['SOURCE_ID']);
$this->SetVariable('assigned_id', $lead['ASSIGNED_BY_ID']);
$this->SetVariable('deal_assigned', $dealAssigned);
