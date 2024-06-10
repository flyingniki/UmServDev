<?php

\CModule::IncludeModule('crm');
\CModule::IncludeModule('tasks');
\CModule::IncludeModule('im');

$obActivities = \CCrmActivity::GetList(
    $arOrder = array(),
    $arFilter = array('OWNER_TYPE_ID' => 2, 'OWNER_ID' => 9430, 'CHECK_PERMISSIONS' => 'N'),
    $arGroupBy = false,
    $arNavStartParams = false,
    $arSelectFields = array('COMPLETED', 'STATUS', 'SETTINGS'),
    $arOptions = array()
);

$arOpenedTasks = [];

while ($arActivity = $obActivities->Fetch()) {
    if (isset($arActivity['SETTINGS']['TASK_ID'])) {
        /**
         * get task array
         */
        $task = new \Bitrix\Tasks\Item\Task($arActivity['SETTINGS']['TASK_ID']);
        $taskData = $task->getData();
        if ($taskData['GROUP_ID'] == 139 && $taskData['STATUS'] == 2) {
            $arOpenedTasks[] = $taskData['ID'];
        }
    }
}

if (!empty($arOpenedTasks)) {
    $arMessage = array(
        "MESSAGE" => "Вы не можете перевести сделку в данную стадию, так как задачи проекта Enterprise не закрыты в сделке",
        "MESSAGE_TYPE" => 'S',
        "TO_USER_ID" => 319,
    );
    \CIMMessenger::Add($arMessage);
}
