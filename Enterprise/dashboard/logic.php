<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$departmentId = 725;
$arSelect = ['ID', 'NAME', 'LAST_NAME'];

$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

while ($rsEmployees = $arEmployees->fetch()) {
    $rsUser = CUser::GetByID($rsEmployees['ID']);
    $arUser = $rsUser->Fetch();
    if ($arUser['ACTIVE'] == 'Y') {
        if (!empty($arUser['PERSONAL_PHOTO'])) {
            $rsFile = CFile::GetByID($arUser['PERSONAL_PHOTO']);
            $arFile = $rsFile->Fetch();
        }
        $users[] = [
            'FULL_NAME' => $arUser['NAME'] . ' ' . $arUser['LAST_NAME'],
            'PHOTO_SRC' => $arFile['SRC'] ?? '',
            'PROGRESS' => (isset($arUser['UF_1C_FACT']) && isset($arUser['UF_1C_PLAN'])) ?
                round(($arUser['UF_1C_FACT'] / $arUser['UF_1C_PLAN'] * 100), 2) :
                null,
            'FACT_VALUE' => $arUser['UF_1C_FACT'],
            'PLAN_VALUE' => $arUser['UF_1C_PLAN']
        ];
    }
}

usort($users, function ($a, $b) {
    return ($b['PROGRESS'] - $a['PROGRESS']);
});
