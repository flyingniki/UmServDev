<?php

/**
 * get user name
 */
function getUserName($userId)
{
    $rsUser = CUser::GetByID($userId);
    $arUser = $rsUser->Fetch();
    $userName = $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
    return $userName;
}

$arFilter = array(
    'CATEGORY_ID' => 13,
    'CHECK_PERMISSIONS' => 'N',
    'CLOSED' => 'N'
);
$arSelect = array('ID', 'TITLE', 'ASSIGNED_BY_ID', 'COMPANY_ID', 'CATEGORY_ID', 'STAGE_ID', 'CLOSED');
$res = \CCrmDeal::GetListEx(array(), $arFilter, false, false, $arSelect, array());
while ($arDeal = $res->Fetch()) {
    $dealArray[$arDeal['ID']] = [
        'TITLE' => $arDeal['TITLE'],
        'ASSIGNED_NAME' => getUserName($arDeal['ASSIGNED_BY_ID'])
    ];
}

foreach ($dealArray as $dealId => $deal) {
    $countNotCompleted = 0;

    $obActivities = \CCrmActivity::GetList(
        $arOrder = array(),
        $arFilter = array('OWNER_TYPE_ID' => 2, 'OWNER_ID' => $dealId, 'CHECK_PERMISSIONS' => 'N'),
        $arGroupBy = false,
        $arNavStartParams = false,
        $arSelectFields = array('COMPLETED', 'STATUS', 'SETTINGS'),
        $arOptions = array()
    );

    while ($arActivity = $obActivities->Fetch()) {
        if ($arActivity['COMPLETED'] == 'N' && isset($arActivity['SETTINGS']['TASK_ID'])) {
            $countNotCompleted++;
        }
    }
    if ($countNotCompleted == 0) {
        $resultLogistics[$dealId] = $deal;
    }
}
