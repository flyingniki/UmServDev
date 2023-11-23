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
            array('STATUS' => [-1, 5], 'CHECK_PERMISSIONS' => 'N'),
            array('ID', 'STATUS', 'DEADLINE', 'UF_*'),
            array()
        );

        while ($arTask = $rsTask->GetNext()) {
            foreach ($arTask['UF_CRM_TASK'] as $crmTask) {
                if (preg_match('/D_/', $crmTask)) {
                    $taskArray[substr($crmTask, 2)] = [
                        'TASK_ID' => $arTask['ID'],
                        'STATUS' => $arTask['STATUS']
                    ];
                }
            }
        }

        $arFilter = array(
            'CATEGORY_ID' => [12, 13, 14],
            'CHECK_PERMISSIONS' => 'N'
        );
        $arSelect = array('ID', 'CATEGORY_ID', 'STAGE_ID');
        $res = \CCrmDeal::GetListEx(array(), $arFilter, false, false, $arSelect, array());
        while ($arDeal = $res->Fetch()) {
            if (!(preg_match('/WON/', $arDeal['STAGE_ID']) || preg_match('/LOSE/', $arDeal['STAGE_ID']))) {
                $dealArray[$arDeal['CATEGORY_ID']][] = [
                    'DEAL_ID' => $arDeal['ID']
                ];
            }
        }

        $result = [];

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
                $result[$categoryId]['SUM']++;
                foreach ($taskArray as $dealId => $task) {
                    if ($dealId == $deal['DEAL_ID']) {
                        if ($task['STATUS'] == 5) {
                            $result[$categoryId]['COMPLETED']++;
                        } else {
                            $result[$categoryId]['EXPIRED']++;
                        }
                    }
                }
            }
        }

        $subject = 'Аналитика по сделкам';
        $message = '';
        foreach ($result as $categoryId => $res) {
            $message .= '<u><b>' . $categoryId . '</b></u><br>' . 'Общее количество сделок с задачами - ' . $res['SUM'] . '. С закрытыми задачами - ' . $res['COMPLETED'] . '. С просроченными задачами - ' . $res['EXPIRED'] . '<br>';
        }

        $headers  = 'Content-type: text/html; charset=UTF-8';

        mail('nk@umserv.ru', $subject, $message, $headers);

        return 'UmServe\Agents\Agent::dealAnalytics();';
    }
}
