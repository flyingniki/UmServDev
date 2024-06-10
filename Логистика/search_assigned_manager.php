<?php

CModule::IncludeModule("crm");

$departmentId = 721; // Менеджеры по закупкам
$arSelect = ['ID', 'NAME', 'LAST_NAME'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

$departmentId = 708; // Отдел закупок и логистики
$directorLogisticsId = CIntranetUtils::GetDepartmentManagerID($departmentId);

while ($rsEmployees = $arEmployees->fetch()) {
    if ($rsEmployees['ID'] == $directorLogisticsId || $rsEmployees['ID'] != 309) { // Только М. Карасева ID: 309
        continue;
    }
    $arEmp[] = 'user_' . $rsEmployees['ID'];
}

$managerId = mb_substr('{{МПП}}', 5);

switch ($managerId) {
    case 46:
        $arEmp = ['user_' . 212];
        $flagText = true;
        break;
    case 8:
        $arEmp = ['user_' . 212];
        $flagText = true;
        break;
    case 325:
        $arEmp = ['user_' . 212];
        $flagText = true;
        break;
    case 327:
        $arEmp = ['user_' . 212];
        $flagText = true;
        break;
    case 273:
        $arEmp = ['user_' . 212];
        $flagText = true;
        break;
    case 321:
        $arEmp = ['user_' . 212];
        $flagText = true;
        break;

    default:
        $flagText = false;
        break;
}

if ($flagText) {
    $taskText = 'Необходим подбор оборудования Enterprise по [url=https://crm.umserv.ru{{Ссылка на элемент}}]сделке[/url] {{Название}}';
} else {
    $taskText = 'Требуется обработка заказа по [url=https://crm.umserv.ru{{Ссылка на элемент}}]сделке[/url] {{Название}}';
}

$directorLogisticsId = 'user_' . $directorLogisticsId;

$this->SetVariable('arEmp', $arEmp);
$this->SetVariable('directorLogisticsId', $directorLogisticsId);
$this->SetVariable('taskText', $taskText);
