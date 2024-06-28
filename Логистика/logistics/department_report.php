<?php

$departmentId = 708; //logistics
$directorId = CIntranetUtils::GetDepartmentManagerID($departmentId);

$bSearchSubs = true;
$dbUserDepartments = CIntranetUtils::getSubordinateEmployees($directorId, $bSearchSubs);

while ($arUser = $dbUserDepartments->Fetch()) {
    $userInfo[$arUser['ID']] = [
        'USER_NAME' => $arUser['NAME'] . ' ' . $arUser['LAST_NAME'],
        'DEPARTMENT_ID' => current($arUser['UF_DEPARTMENT']),
        'DEPARTMENT_NAME' => current(CIntranetUtils::GetDepartmentsData([current($arUser['UF_DEPARTMENT'])]))
    ];
}

/**
 * search responsible role
 */
foreach ($userInfo as $userId => $user) {
    $res = CTasks::GetList(
        $arOrder = array('ID' => 'DESC'),
        $arFilter = array(
            '::LOGIC' => 'AND',
            'CHECK_PERMISSIONS' => 'N',
            'RESPONSIBLE_ID' => $userId,
            '::SUBFILTER-1' => array(
                '::LOGIC' => 'OR',
                'REAL_STATUS' => array(CTasks::STATE_PENDING, CTasks::STATE_IN_PROGRESS),
                'STATUS' => -1
            ),

        ),
        $arSelect = array('ID', 'REAL_STATUS', 'STATUS'),
        $arParams = array()
    );

    $countExpired = 0;
    $countInProgress = 0;
    while ($arTask = $res->Fetch()) {
        if ($arTask['STATUS'] == -1) {
            $countExpired++;
        }
        if ($arTask['REAL_STATUS']) {
            $countInProgress++;
        }
    }

    $user['RESPONSIBLE_COUNT'] = $countInProgress;
    $user['EXPIRED_RESPONSIBLE_COUNT'] = $countExpired;
    $resultDepartment[$userId] = $user;

    /**
     * search accomplice role
     */
    $res = CTasks::GetList(
        $arOrder = array('ID' => 'DESC'),
        $arFilter = array(
            '::LOGIC' => 'AND',
            'CHECK_PERMISSIONS' => 'N',
            'ACCOMPLICE' => $userId,
            '::SUBFILTER-1' => array(
                '::LOGIC' => 'OR',
                'REAL_STATUS' => array(CTasks::STATE_PENDING, CTasks::STATE_IN_PROGRESS),
                'STATUS' => -1
            ),

        ),
        $arSelect = array('ID', 'REAL_STATUS', 'STATUS'),
        $arParams = array()
    );

    $countExpired = 0;
    $countInProgress = 0;
    while ($arTask = $res->Fetch()) {
        if ($arTask['STATUS'] == -1) {
            $countExpired++;
        }
        if ($arTask['REAL_STATUS']) {
            $countInProgress++;
        }
    }

    $user['ACCOMPLICE_COUNT'] = $countInProgress;
    $user['EXPIRED_ACCOMPLICE_COUNT'] = $countExpired;
    $resultDepartment[$userId] = $user;

    /**
     * search auditor role
     */
    $res = CTasks::GetList(
        $arOrder = array('ID' => 'DESC'),
        $arFilter = array(
            '::LOGIC' => 'AND',
            'CHECK_PERMISSIONS' => 'N',
            'AUDITOR' => $userId,
            '::SUBFILTER-1' => array(
                '::LOGIC' => 'OR',
                'REAL_STATUS' => array(CTasks::STATE_PENDING, CTasks::STATE_IN_PROGRESS),
                'STATUS' => -1
            ),

        ),
        $arSelect = array('ID', 'REAL_STATUS', 'STATUS'),
        $arParams = array()
    );

    $countExpired = 0;
    $countInProgress = 0;
    while ($arTask = $res->Fetch()) {
        if ($arTask['STATUS'] == -1) {
            $countExpired++;
        }
        if ($arTask['REAL_STATUS']) {
            $countInProgress++;
        }
    }

    $user['AUDITOR_COUNT'] = $countInProgress;
    $user['EXPIRED_AUDITOR_COUNT'] = $countExpired;
    $resultDepartment[$userId] = $user;
}

/**
 * sort by DEPARTMENT_ID
 */
usort($resultDepartment, function ($a, $b) {
    return ($a['DEPARTMENT_ID'] - $b['DEPARTMENT_ID']);
});