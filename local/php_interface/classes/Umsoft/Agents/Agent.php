<?php

namespace Umserv\Umsoft\Agents;

class Agent
{
    public static function dealAnalytics()
    {
        \CModule::IncludeModule('crm');
        \CModule::IncludeModule('im');
        \CModule::IncludeModule('tasks');

        $arFilter = array(
            'CATEGORY_ID' => [12, 13, 14],
            'CHECK_PERMISSIONS' => 'N'
        );
        $arSelect = array('ID', 'CATEGORY_ID', 'STAGE_ID', 'CLOSED');
        $res = \CCrmDeal::GetListEx(array(), $arFilter, false, false, $arSelect, array());
        while ($arDeal = $res->Fetch()) {
            if ($arDeal['CLOSED'] == 'N') {
                $dealArray[$arDeal['CATEGORY_ID']][] = [
                    'ID' => $arDeal['ID'],
                    'STAGE_ID' => $arDeal['STAGE_ID']
                ];
            }
        }

        $result = [];
        $final = [];

        foreach ($dealArray as $categoryId => $arCategory) {
            foreach ($arCategory as $deal) {
                switch ($categoryId) {
                    case 12:
                        $categoryId = 'Розница';
                        break;

                    case 13:
                        $categoryId = 'Логистика';
                        break;

                    case 14:
                        $categoryId = 'Тендеры';
                        break;

                    default:
                        break;
                }
                // deal sum in this category
                $final[$categoryId]['SUM']++;
                $obActivities = \CCrmActivity::GetList(
                    $arOrder = array(),
                    $arFilter = array('OWNER_TYPE_ID' => 2, 'OWNER_ID' => $deal['ID'], 'CHECK_PERMISSIONS' => 'N'),
                    $arGroupBy = false,
                    $arNavStartParams = false,
                    $arSelectFields = array('COMPLETED', 'STATUS', 'SETTINGS'),
                    $arOptions = array()
                );

                while ($arActivity = $obActivities->Fetch()) {
                    if (isset($arActivity['SETTINGS']['ACTIVITY_STATUS'])) {
                        if ($deal['STAGE_ID'] != 'C14:UC_MFDJSO' || $deal['STAGE_ID'] != 'C12:UC_3WSNRP') {
                            $result[$categoryId][$deal['ID']][] = $arActivity['SETTINGS']['ACTIVITY_STATUS'];
                        }
                    }
                }
            }
        }

        foreach ($result as $categoryId => $arDealAcivity) {
            foreach ($arDealAcivity as $dealID => $arStatus) {
                $arStatus = array_unique($arStatus);
                if (in_array('EXPIRED', $arStatus)) {
                    $final[$categoryId]['EXPIRED']++;
                    $final[$categoryId]['DEAL_ID_EXPIRED'][] = $dealID;
                }
                $completed = [];
                foreach ($arStatus as $status) {
                    if ($status != 'FINISHED') {
                        $completed[] = $status;
                    }
                }
                if (empty($completed)) {
                    $final[$categoryId]['COMPLETED']++;
                    $final[$categoryId]['DEAL_ID_COMPLETED'][] = $dealID;
                }
            }
        }

        $subject = 'Аналитика по сделкам';

        $fullReport = '';
        $logisticsReport = '';
        $saleReport = '';
        foreach ($final as $categoryId => $res) {
            $fullReport .= '<u><b>' . $categoryId . '</b></u><br>' . 'Всего сделок в работе - ' . $res['SUM'] . '. Сделки без открытых задач - ' . $res['COMPLETED'] . '. С просроченными задачами - ' . $res['EXPIRED'] . '<br>';
            if ($categoryId == 'Логистика') {
                $logisticsReport .= '<u><b>' . $categoryId . '</b></u><br>' . 'Всего сделок в работе - ' . $res['SUM'] . '. Сделки без открытых задач - ' . $res['COMPLETED'] . '. С просроченными задачами - ' . $res['EXPIRED'] . '<br>';
            } elseif (in_array($categoryId, ['Розница', 'Тендеры'])) {
                $saleReport .= '<u><b>' . $categoryId . '</b></u><br>' . 'Всего сделок в работе - ' . $res['SUM'] . '. Сделки без открытых задач - ' . $res['COMPLETED'] . '. С просроченными задачами - ' . $res['EXPIRED'] . '<br>';
            }
        }

        $headers  = 'Content-type: text/html; charset=UTF-8';

        $arEmail = ['nk@umserv.ru', 'derkach@umserv.ru'];

        foreach ($arEmail as $email) {
            mail($email, $subject, $fullReport, $headers);
        }

        return 'Umserv\Umsoft\Agents\Agent::dealAnalytics();';
    }

    public static function getArrayDealID()
    {
        \CModule::IncludeModule('crm');

        $arFilter = array(
            'CATEGORY_ID' => 15,
            'CLOSED' => 'N',
            'CHECK_PERMISSIONS' => 'N'
        );
        $arSelect = array('ID', 'CATEGORY_ID', 'STAGE_ID');
        $res = \CCrmDeal::GetListEx(array('ID' => 'DESC'), $arFilter, false, false, $arSelect, array());
        while ($arDeal = $res->Fetch()) {
            // print_r($arDeal);
            $arDealID[] = $arDeal['ID'];
        }

        return $arDealID;
    }

    public static function getCommentsList($entityId)
    {
        $dbResult = \Bitrix\Crm\Timeline\Entity\TimelineTable::getList(array(
            'order' => array('ID' => 'DESC'),
            'select' => array('ID', 'COMMENT', 'AUTHOR_ID', 'CREATED', 'ASSOCIATED_ENTITY_ID'),
            'filter' => ['TYPE_ID' => 7, 'BINDINGS.ENTITY_ID' => $entityId],
        ));
        while ($fields = $dbResult->fetch()) {
            $commentList[] = is_array($fields) && $fields['COMMENT'] ? [
                'ID' => $fields['ID'],
                'COMMENT' => $fields['COMMENT'],
                'AUTHOR_ID' => $fields['AUTHOR_ID'],
                'DATE_CREATE' => $fields['CREATED']->toString(new \Bitrix\Main\Context\Culture(array()))
            ] : null;
        }

        return $commentList;
    }

    public static function getUserName()
    {
        $by = 'id';
        $order = 'ASC';
        $filter = array('ACTIVE' => 'Y', 'GROUPS_ID' => array(11));
        $arParams = array('FIELDS' => array('ID', 'NAME', 'LAST_NAME'));

        $rsUsers = \CUser::GetList($by, $order, $filter, $arParams);

        while ($user = $rsUsers->Fetch()) {
            $arUserName[$user['ID']] = $user['NAME'] . ' ' . $user['LAST_NAME'];
        }

        return $arUserName;
    }

    public static function commentReport()
    {
        $arDealID = self::getArrayDealID();
        $arUserName = self::getUserName();
        foreach ($arDealID as $dealID) {
            $list[$dealID] = self::getCommentsList($dealID);
        }

        $commentReport = '';

        foreach ($list as $dealID => $arComments) {
            $commentReport .= '<b>ID  сделки:</b> ' . $dealID . '<br>';
            foreach ($arComments as $comment) {
                $commentReport .= '<u>ID</u>: ' . $comment['ID'] . '<br>' . '<u>Комментарий</u>: ' . $comment['COMMENT'] . '<br>' . '<u>Дата создания</u>: ' . $comment['DATE_CREATE'] . '<br>' . '<u>Автор</u>: ' . $arUserName[$comment['AUTHOR_ID']] . '<br><br>';
            }
        }

        $subject = 'Отчет по комментариям в воронке Enterprise';
        $headers  = 'Content-type: text/html; charset=UTF-8';
        $arEmail = ['nk@umserv.ru'];

        foreach ($arEmail as $email) {
            mail($email, $subject, $commentReport, $headers);
        }

        return 'Umserv\Umsoft\Agents\Agent::commentReport();';
    }
}
