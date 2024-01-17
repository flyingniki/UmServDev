<?php

echo '<pre>';

if (CModule::IncludeModule("tasks")) {
    $res = CTasks::GetList(
        $arOrder = array('ID' => 'ASC'),
        $arFilter = array(
            '::LOGIC' => 'AND',
            'CHECK_PERMISSIONS' => 'N',
            '::SUBFILTER-1' => array(
                '::LOGIC' => 'OR',
                '::SUBFILTER-1' => array(
                    'ACCOMPLICE' => array(309),
                    'REAL_STATUS' => array(CTasks::STATE_NEW, CTasks::STATE_PENDING, CTasks::STATE_IN_PROGRESS),
                ),
                '::SUBFILTER-2' => array(
                    'AUDITOR' => array(309),
                    'REAL_STATUS' => array(CTasks::STATE_NEW, CTasks::STATE_PENDING, CTasks::STATE_IN_PROGRESS),
                ),
            ),
        ),
        $arSelect = array('ID'),
        $arParams = array()
    );

    while ($arTask = $res->Fetch()) {
        $arTaskID[] = $arTask['ID'];
    }

    $arTaskID = [10114, 12054, 8113, 5890, 14705, 5431, 8647, 13881, 14492, 13632, 12907, 12906, 16759, 1739, 3413, 5212, 5516, 8529, 11888, 12102, 12602, 12601, 12427, 12426, 12929, 12097, 12905, 13158, 12542, 12425, 12424, 12603, 3733, 12420, 12343, 12689, 12156, 13015, 12701, 10928, 2769, 15282, 4400, 2623, 3998, 3435, 2705, 4815];

    print_r($arTaskID);

    foreach ($arTaskID as $taskId) {
        $task = new \Bitrix\Tasks\Item\Task($taskId);

        $auditors = $task->getData()['AUDITORS']->toArray();
        echo 'AUDITORS_BEFORE';
        print_r($auditors);

        if (in_array(309, $auditors)) {
            unset($auditors[array_search(309, $auditors)]);
        }

        echo 'AUDITORS_AFTER';
        print_r($auditors);

        $accomplices = $task->getData()['ACCOMPLICES']->toArray();
        echo 'ACCOMPLICES_BEFORE';
        print_r($accomplices);

        if (in_array(309, $accomplices)) {
            unset($accomplices[array_search(309, $accomplices)]);
        }

        echo 'ACCOMPLICES_AFTER';
        print_r($accomplices);

        $oTaskItem = new CTaskItem($taskId, 309);

        try {
            $rs = $oTaskItem->Update(array("AUDITORS" => $auditors, 'ACCOMPLICES' => $accomplices));
        } catch (Exception $e) {
            print('Error');
        }
    }
}
