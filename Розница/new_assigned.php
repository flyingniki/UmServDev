<?php

CModule::IncludeModule('crm');

$departmentId = 720; // МПП
$arSelect = ['ID', 'NAME', 'LAST_NAME'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

$departmentId = 314; // РОП
$managerId = CIntranetUtils::GetDepartmentManagerID($departmentId);

// при создании сделки лидом обязательно заполнить поле {{Создатель сделки}} == ответственный за лид
$createdBy = '{{Создатель сделки}}';

while ($rsEmployees = $arEmployees->fetch()) {
    if ($rsEmployees['ID'] == $managerId) {
        continue;
    }
    $arEmp[] = 'user_' . $rsEmployees['ID'];
}

if (in_array($createdBy, $arEmp) || $createdBy == 'user_' . $managerId) {
    $arEmp = [];
    $arEmp[] = $createdBy;
}

$managerId = 'user_' . $managerId;

// поиск сделок в воронке Розница c таким же контактом
$assignedId = '';
$contactId = '{{Контакт}}';
$arFilter = array(
    '!ID' => '{{ID}}',
    'CATEGORY_ID' => 12,
    'CHECK_PERMISSIONS' => 'N'
);
$arSelect = array();

$res = CCrmDeal::GetListEx(array('DATE_CREATE' => 'ASC'), $arFilter, false, false, $arSelect, array());

while ($row = $res->GetNext()) {
    if ($contactId && $contactId == $row['CONTACT_ID']) {
        $assignedId = $row['ASSIGNED_BY_ID'];
    }
}

if ($assignedId && in_array($assignedId, $arEmp)) {
    $arEmp = [];
    $arEmp[] = 'user_' . $assignedId;
}

$this->SetVariable('createdBy', $createdBy);
$this->SetVariable('managerId', $managerId);
$this->SetVariable('arEmp', $arEmp);
