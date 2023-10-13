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
            \CModule::IncludeModule('im');
            $arFields = array(
                "MESSAGE" => "Вы не можете добавить задачу без указания крайнего срока",
                "MESSAGE_TYPE" => 'S',
                "TO_USER_ID" => $data['CREATED_BY'],
            );
            \CIMMessenger::Add($arFields);
            throw new SystemException("Вы не можете добавить задачу без указания крайнего срока");
        }

        // for logistics department only
        $rsUser = \CUser::GetByID($data['RESPONSIBLE_ID']);
        $arUser = $rsUser->Fetch();
        $userDeps = $arUser['UF_DEPARTMENT'];
        // subdepartments included in the main one
        $depsList = [711, 712, 713, 721];
        if (!empty($userDeps)) {
            foreach ($userDeps as $depId) {
                if (in_array($depId, $depsList) && !in_array(274, $data['AUDITORS'])) {
                    $data['AUDITORS'][] = 274; //add manager of logistics like auditor
                }
            }
        }
    }

    public static function OnTaskAddHandler($idTask, $arTask)
    {
        /**
         * Update deal if deal category is 12 and task is binded
         */
        \CModule::IncludeModule('crm');
        $arCrmTask = $arTask['UF_CRM_TASK'];
        foreach ($arCrmTask as $crmTask) {
            if (preg_match('/D_/', $crmTask)) {
                $dealId = substr($crmTask, 2);
                $arFilter = array(
                    "ID" => $dealId,
                    "CHECK_PERMISSIONS" => "N",
                    "CATEGORY_ID" => 12
                );
                $arSelect = array('ID', 'CATEGORY_ID');
                $res = \CCrmDeal::GetListEx(array(), $arFilter, false, false, $arSelect, array());
                if ($arDeal = $res->Fetch()) {
                    // change deal field UF_CRM_1696705078
                    $bCheckRight = false;
                    $entityFields = [
                        'UF_CRM_1696705078'   => $idTask,
                    ];
                    $entityObject = new \CCrmDeal($bCheckRight);
                    $isUpdateSuccess = $entityObject->Update(
                        $dealId,
                        $entityFields,
                        $bCompare = true,
                        $arOptions = []
                    );
                }
            }
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
