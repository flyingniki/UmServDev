<?php

$by = 'id';
$order = 'ASC';
$filter = array('ACTIVE' => 'Y', 'GROUPS_ID' => array(11));
$arParams = array();

$rsUsers = CUser::GetList($by, $order, $filter, $arParams);

while ($user = $rsUsers->Fetch()) {
    if ($user['ID'] == 319) :
        print_r($user);
        $login = $user['LOGIN'];
    endif;
}
