<?php

namespace Umserv\Umsoft\Tasks;

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
        \CModule::IncludeModule('task');
        \CModule::IncludeModule('crm');
        $rsTask = \CTasks::GetByID($id);
        if ($arTask = $rsTask->Fetch()) {
            $userGroups = User::getGroups($data['CHANGED_BY']);

            /**
             * Restrict to change task deadline by assigned user
             */
            if (
                $data['DEADLINE'] &&
                (strtotime($data['DEADLINE']) != strtotime($arTask['DEADLINE'])) &&
                !array_key_exists('ID_1', $userGroups) &&
                ($data['CHANGED_BY'] != $arTask['CREATED_BY'])
            ) {
                throw new SystemException("Вы не можете изменить крайний срок у задачи");
            }
            
            /**
             * disable editing if task category == 0 and && user department is from [721, 725]
             */
            $crmFields = $arTask['UF_CRM_TASK'];
            if (!empty($crmFields)) {
                $rsUser = \CUser::GetByID($data['CHANGED_BY']);
                $arUser = $rsUser->Fetch();
                $arDepartments = $arUser['UF_DEPARTMENT'];
                foreach ($crmFields as $crmField) {
                    if (preg_match('/D_/', $crmField)) {
                        $dealId = substr($crmField, 2);
                        $deal = \CCrmDeal::GetById($dealId);
                        if ($deal['CATEGORY_ID'] == 0) {
                            $userBelongs = array_intersect($arDepartments, [721, 725]);
                            if (!empty($userBelongs)) {
                                throw new SystemException('Доступ закрыт');
                            }
                        }
                    }
                }
            }
        }
    }
}
