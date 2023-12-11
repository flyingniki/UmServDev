<?php

namespace UmServe\Agents;

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
            foreach ($arDealAcivity as $arStatus) {
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

        mail('grigoriev@umserv.ru', $subject, $logisticsReport, $headers);
        mail('zaharov@umserv.ru', $subject, $saleReport, $headers);

        return 'UmServe\Agents\Agent::dealAnalytics();';
    }
}
