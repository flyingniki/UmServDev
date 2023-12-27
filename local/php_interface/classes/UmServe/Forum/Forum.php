<?php

namespace  UmServe\Forum;

use Bitrix\Main\SystemException;

class Forum
{
    public static function onBeforeMessageAddHandler($arFields)
    {
        $taskId = substr($arFields['XML_ID'], 5);
        $userDepartments = Forum::getUserDepartments($arFields['AUTHOR_ID']);
        $arDealIds = Forum::getBindedDealsFromTask($taskId);
        foreach ($arDealIds as $dealId) {
            $dealCategory = Forum::getDealCategory($dealId);
            if ($dealCategory == 0) {
                $userBelongs = array_intersect($userDepartments, [721, 725]);
                if (!empty($userBelongs)) {
                    throw new SystemException('Доступ закрыт');
                }
            }
        }
    }

    public static function getDealCategory($id)
    {
        \CModule::IncludeModule('crm');
        $deal = \CCrmDeal::GetById($id);
        return $deal['CATEGORY_ID'];
    }

    public static function getUserDepartments($id)
    {
        $rsUser = \CUser::GetByID($id);
        $arUser = $rsUser->Fetch();
        $arDepartments = $arUser['UF_DEPARTMENT'];
        return $arDepartments;
    }

    public static function getBindedDealsFromTask($id)
    {
        \CModule::IncludeModule('task');
        $rsTask = \CTasks::GetByID($id);
        if ($arTask = $rsTask->Fetch()) {
            $crmFields = $arTask['UF_CRM_TASK'];
            foreach ($crmFields as $crmField) {
                if (preg_match('/D_/', $crmField)) {
                    $arDealIds[] = substr($crmField, 2);
                    return $arDealIds;
                }
            }
        }
    }
}
