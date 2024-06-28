<?php

declare(strict_types=1);
/** Регистрация автозагрузки классов */

try {
    \Bitrix\Main\Loader::registerAutoLoadClasses(null, []);
} catch (\Bitrix\Main\LoaderException $e) {
    ShowError('Выброшено исключение при регистрации класса. файл: ' . __FILE__);
}
