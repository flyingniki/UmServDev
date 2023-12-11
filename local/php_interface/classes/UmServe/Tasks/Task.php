<?php

namespace UmServe\Tasks;

use Bitrix\Main\SystemException;
use Flah\Tools\User;

class Task
{
    public static function OnBeforeTaskAddHandler(&$data)
    {
        /**
         * Task add is not allowed if deadline is empty
         */
        if (empty($data['DEADLINE']) && $data['REPLICATE'] == false) {
            $data['DEADLINE'] = date('d.m.Y 18:00:00');
            \CModule::IncludeModule('im');
            $arFields = array(
                "MESSAGE" => "Крайний срок задачи установлен по умолчанию: {$data['DEADLINE']}",
                "MESSAGE_TYPE" => 'S',
                "TO_USER_ID" => $data['CREATED_BY'],
            );
            \CIMMessenger::Add($arFields);
        }
    }

    public static function OnBeforeTaskUpdateHandler($id, &$data, &$arTaskCopy)
    {
        /**
         * Restrict to change task deadline by assigned user
         */
        \CModule::IncludeModule('task');
        $rsTask = \CTasks::GetByID($id);
        if ($arTask = $rsTask->GetNext()) {
            $userGroups = User::getGroups($data['CHANGED_BY']);
            if (
                $data['DEADLINE'] &&
                (strtotime($data['DEADLINE']) != strtotime($arTask['DEADLINE'])) &&
                !array_key_exists('ID_1', $userGroups) &&
                ($data['CHANGED_BY'] != $arTask['CREATED_BY'])
            ) {
                throw new SystemException("Вы не можете изменить крайний срок у задачи");
            }
        }
    }
}
