<?php

CModule::IncludeModule('crm');

$dealId = '{{ID}}';
$arFilter = array(
    'ID' => $dealId,
    'CHECK_PERMISSIONS' => 'N',
);
$arSelect = array('UF_CRM_1715934705');

$res = CCrmDeal::GetListEx(array('ID' => 'DESC'), $arFilter, false, false, $arSelect, array());
while ($arDeal = $res->Fetch()) {
    $directions = $arDeal['UF_CRM_1715934705'];
}

$mainDepartmentId = 729;
$mainProductManagerId = CIntranetUtils::GetDepartmentManagerID($mainDepartmentId);
/**
 * get department ID
 */
if (!empty($directions)) {
    foreach ($directions as $direction) {
        $departmentsId[] = match ($direction) {
            2021 => 771,
            2022 => 772,
            2023 => 780,
            2024 => 773,
            default => '',
        };
    }
    $this->SetVariable('departments', $departmentsId);
}
$mainProductManagerId = 'user_' . $mainProductManagerId;
$this->SetVariable('mainProductManagerId', $mainProductManagerId);
