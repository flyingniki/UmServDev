<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('crm');

/**
 * stages exeptions
 * C15:UC_XKB379, C15:UC_B9Y250, C15:UC_94DGF7, C15:LOSE, C15:WON
 */

$arFilter = array(
    'CATEGORY_ID' => 15,
    '>=UF_CRM_1700635161' => date('d.m.Y'),
    'CHECK_PERMISSIONS' => 'N',
);
$arSelect = array('ID', 'UF_CRM_1700635161', 'TITLE', 'ASSIGNED_BY_NAME', 'ASSIGNED_BY_LAST_NAME', 'STAGE_ID');
$res = \CCrmDeal::GetListEx(array('UF_CRM_1700635161' => 'ASC', 'ID' => 'ASC'), $arFilter, false, false, $arSelect, array());
while ($arDeal = $res->Fetch()) {
    if (!in_array($arDeal['STAGE_ID'], ['C15:UC_XKB379', 'C15:UC_B9Y250', 'C15:UC_94DGF7', 'C15:LOSE', 'C15:WON'])) {
        $dealsReport[] = $arDeal;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет по сделкам Enterprise</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <table class="table">
            <caption>Отчет по сделкам Enterprise</caption>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Номер сделки</th>
                    <th>Тендер</th>
                    <th>Ответственный</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($dealsReport as $key => $deal) {
                    if ($key < 10) {
                        if ($deal['STAGE_ID'] == 'C15:UC_30C6SS') {
                            $status = 'Готов к подаче';
                            $statusColor = '#228B22';
                        } else {
                            $status = 'Не готов';
                            $statusColor = '#CD5C5C';
                        }
                ?>
                        <tr style="background-color: <?= $statusColor ?>;">
                            <td><?= $deal['UF_CRM_1700635161'] ?></td>
                            <td><a href="https://crm.umserv.ru/crm/deal/details/<?= $deal['ID'] ?>/"><?= $deal['ID'] ?></a></td>
                            <td><?= $deal['TITLE'] ?></td>
                            <td><?= $deal['ASSIGNED_BY_NAME'] . ' ' . $deal['ASSIGNED_BY_LAST_NAME'] ?></td>
                            <td><?= $status ?></td>
                        </tr>
                    <? } ?>
                <? } ?>
            </tbody>
        </table>
    </div>
</body>

</html>