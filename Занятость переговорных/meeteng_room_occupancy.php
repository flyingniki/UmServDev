<?php

\CModule::IncludeModule('calendar');

$resEvents  = CCalendarEvent::GetList();

$diff = [];
foreach ($resEvents as $event) {
    if (in_array($event['SECTION_ID'], [43, 84])) {
        $dateFrom = $event['DATE_FROM'];
        $dateTo = $event['DATE_TO'];
        $roomID = $event['SECTION_ID'];
        if ($roomID == 43) {
            $roomID = 'Коммерческая переговорная';
        } else {
            $roomID = 'Сервисная переговорная';
        }
        // $diff[] = ($dateTo - $dateFrom) / 3600;
        $result[] = [
            'ROOM' => $roomID,
            'DATE_FROM' => $dateFrom,
            'DATE_TO' => $dateTo
        ];
    }
}
// print_r($result);
// file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/room-report.txt', print_r($result, true));
// $sum = array_sum($diff);
// echo 'Суммарная занятость переговорных: ' . $sum;
$fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/room-report.csv', 'w');
foreach ($result as $fields) {
    fputcsv($fp, $fields);
}
fclose($fp);
