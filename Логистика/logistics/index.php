<?php

require_once './logic.php';

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Refresh" content="3600">
    <meta http-equiv="cache-control" content="no-cache">
    <title>Отчет по отделу Логистика</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="table-container">
            <table class="table table-task">
                <caption class="table__caption">Отчет по задачам сотрудников департамента "Логистика"</caption>
                <thead class="table__thead">
                    <tr class="table__tr">
                        <th class="table__th">Сотрудник</th>
                        <th class="table__th">Делаю</th>
                        <th class="table__th">Помогаю</th>
                        <th class="table__th">Наблюдаю</th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <? foreach ($resultDepartment as $id => $user) { ?>
                        <tr class="table__tr">
                            <td class="table__td"><?= $user['USER_NAME'] ?></td>
                            <td class="table__td">
                                <p class="table__p"><?= $user['RESPONSIBLE_COUNT'] ?></p>
                                <sup class="table__sup"><?= $user['EXPIRED_RESPONSIBLE_COUNT'] ?></sup>
                            </td>
                            <td class="table__td">
                                <p class="table__p"><?= $user['ACCOMPLICE_COUNT'] ?></p>
                                <sup class="table__sup"><?= $user['EXPIRED_ACCOMPLICE_COUNT'] ?></sup>
                            </td>
                            <td class="table__td">
                                <p class="table__p"><?= $user['AUDITOR_COUNT'] ?></p>
                                <sup class="table__sup"><?= $user['EXPIRED_AUDITOR_COUNT'] ?></sup>
                            </td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
            <table class="table table-logistics">
                <caption class="table__caption">Отчет по воронке "Логистика"</caption>
                <thead class="table__thead">
                    <tr class="table__tr">
                        <th class="table__th">ID сделки</th>
                        <th class="table__th">Название</th>
                        <th class="table__th">Ответственный</th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <? foreach ($resultLogistics as $dealId => $deal) { ?>
                        <tr class="table__tr">
                            <td class="table__td"><?= $dealId ?></td>
                            <td class="table__td"><?= $deal['TITLE'] ?></td>
                            <td class="table__td"><?= $deal['ASSIGNED_NAME'] ?></td>
                            </td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>