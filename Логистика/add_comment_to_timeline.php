<?php

CModule::IncludeModule("crm");

$resId = \Bitrix\Crm\Timeline\CommentEntry::create(
    array(
        'TEXT' => 'Доставка товара по [url=https://crm.umserv.ru{{Ссылка на элемент}}]сделке[/url] {{Название}} в воронке {{Направление (текст)}} осуществлена',
        'SETTINGS' => array(),
        'AUTHOR_ID' => '{{Ответственный}}', //ID пользователя, от которого будет добавлен комментарий
        'BINDINGS' => array(array('ENTITY_TYPE_ID' => CCrmOwnerType::Deal, 'ENTITY_ID' => '{{Сделка в воронке Enterprise}}'))
    )
);

$resultUpdating = Bitrix\Crm\Timeline\Entity\TimelineBindingTable::update(
    array('OWNER_ID' => $resId, 'ENTITY_ID' => '{{Сделка в воронке Enterprise}}', 'ENTITY_TYPE_ID' => CCrmOwnerType::Deal),
    array('IS_FIXED' => 'N ')
);
