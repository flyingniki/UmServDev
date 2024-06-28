<?php

require_once './logic.php';

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <title>Отчет по выручке</title>
</head>

<body>
    <div class="table">
        <? foreach ($users as $raiting => $user) {
            $raiting++;
        ?>
            <div class="row">
                <div class="row__raiting rating">
                    <span class="rating__value"><?= $raiting ?></span>
                </div>
                <div class="row__photo photo">
                    <img src="<?= $user['PHOTO_SRC'] ?>" alt="photo" class="photo__img" />
                </div>
                <div class="row__name name">
                    <span class="name__value"><?= $user['FULL_NAME'] ?></span>
                </div>
                <div class="row__scale scale">
                    <progress class="scale__progress" value="<?= $user['PROGRESS'] ?>" max="100"></progress>
                    <span class="scale__value">
                        <?= $user['PROGRESS'] ? $user['PROGRESS'] . '%' : 'нет данных' ?>
                    </span>
                </div>
                <div class="row__plan plan">
                    <span class="plan__value">
                        <?= $user['FACT_VALUE'] && $user['PLAN_VALUE'] ?
                            $user['FACT_VALUE'] . ' / ' . $user['PLAN_VALUE'] :
                            'нет данных' ?>
                    </span>
                </div>
            </div>
        <? } ?>
    </div>
</body>

</html>