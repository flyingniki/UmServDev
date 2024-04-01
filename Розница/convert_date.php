<?php

$timestamp = '{{Timestamp(order)}}';
if ($timestamp) {
    $dateTime = \Bitrix\Main\Type\DateTime::createFromTimestamp($timestamp);
    $this->SetVariable('dateTime', $dateTime);
}
