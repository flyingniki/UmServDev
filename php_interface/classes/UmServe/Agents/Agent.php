<?php

namespace UmServe\Agents;

class Agent
{
    public static function dealAnalytics()
    {
        \CModule::IncludeModule('crm');
        \CModule::IncludeModule('im');
        \CModule::IncludeModule('tasks');

        $rsTask = \CTasks::GetList(
            array(),
            array('STATUS' => [-1, 5]),
            array('ID', 'STATUS', 'DEADLINE', 'UF_*'),
            array()
        );

        while ($arTask = $rsTask->GetNext()) {
            foreach ($arTask['UF_CRM_TASK'] as $crmTask) {
                if (preg_match('/D_/', $crmTask)) {
                    $arFilter = array(
                        'ID' => substr($crmTask, 2),
                        'CATEGORY_ID' => [12, 13, 14]
                    );
                    $arSelect = array('ID', 'CATEGORY_ID');
                    $res = \CCrmDeal::GetListEx(array(), $arFilter, false, false, $arSelect, array());
                    while ($arDeal = $res->Fetch()) {
                        $finalDeals[$arDeal['CATEGORY_ID']][] = [
                            'DEAL_ID' => $arDeal['ID'],
                            'TASK_ID' => $arTask['ID'],
                            'TASK_STATUS' => $arTask['STATUS'],
                            'DEADLINE' => $arTask['DEADLINE']
                        ];
                    }
                }
            }
        }

        foreach ($finalDeals as $categoryId => $arDeals) {
            $sum = count($arDeals);
            $completed = 0;
            $expired = 0;
            foreach ($arDeals as $arDeal) {
                if ($arDeal['TASK_STATUS'] == 5) {
                    $completed++;
                } else {
                    $expired++;
                }
            }

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

            $result[$categoryId] = [
                'SUM' => $sum,
                'COMPLETED' => $completed,
                'EXPIRED' => $expired
            ];
        }

        $subject = 'Аналитика по сделкам';
        $message = '';
        foreach ($result as $categoryId => $res) {
            $message .= '<u><b>' . $categoryId . '</b></u><br>' . 'Общее количество - ' . $res['SUM'] . '. С закрытыми задачами - ' . $res['COMPLETED'] . '. С просроченными задачами - ' . $res['EXPIRED'] . '<br>';
        }

        $headers  = 'Content-type: text/html; charset=UTF-8\r\n';

        mail('nk@umserv.ru', $subject, $message, $headers);

        return 'UmServe\Agents\Agent::dealAnalytics();';
    }
}
