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
// add managerId to arEmp
$arEmp[] = $managerId;

// if incoming call && in_array($lead['ASSIGNED_BY_ID'], $arEmp)
if (in_array($lead['SOURCE_ID'], [1, 2, 6]) && in_array($lead['ASSIGNED_BY_ID'], $arEmp)) {
    $dealAssigned = 'user_' . $lead['ASSIGNED_BY_ID'];
    // get phone number
    $dbResMultiFields = CCrmFieldMulti::GetList(
        array('ID' => 'asc'),
        array('ENTITY_ID' => 'LEAD', 'ELEMENT_ID' => '{{ID}}', 'TYPE_ID' => 'PHONE')
    );
    $resMultiFields = $dbResMultiFields->fetch();
    $phone = preg_replace('/[^0-9]/', '', $resMultiFields['VALUE']);

    // get open deal list
    $arFilter = array(
        'CLOSED' => 'N',
        'CATEGORY_ID' => 12,
        'CHECK_PERMISSIONS' => 'N'
    );
    $arSelect = array();
    $res = CCrmDeal::GetListEx(array('DATE_CREATE' => 'ASC'), $arFilter, false, false, $arSelect, array());
    while ($row = $res->GetNext()) {
        if ($row['CONTACT_ID']) {
            $arContactId[] = $row['CONTACT_ID'];
        }
    }

    foreach ($arContactId as $contactId) {
        $dbResMultiFields = CCrmFieldMulti::GetList(
            array('ID' => 'asc'),
            array('ENTITY_ID' => 'CONTACT', 'ELEMENT_ID' => $contactId, 'TYPE_ID' => 'PHONE')
        );
        while ($resMultiFields = $dbResMultiFields->GetNext()) {
            $arContactPhones[] = preg_replace('/[^0-9]/', '', $resMultiFields['VALUE']);
        }
    }

    if (!in_array($phone, $arContactPhones)) {
        echo 'convert lead';
        $convert = true;
    } else {
        echo "don't convert lead";
        $convert = false;
    }
}

$this->SetVariable('source_id', $lead['SOURCE_ID']);
$this->SetVariable('assigned_id', $lead['ASSIGNED_BY_ID']);
$this->SetVariable('deal_assigned', $dealAssigned);
$this->SetVariable('convert', $convert);
