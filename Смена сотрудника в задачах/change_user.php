<?php

if (CModule::IncludeModule("tasks")) {
    $by = "id";
    $order = "ASC";
    $filter = array("ACTIVE" => "N");
    $arParams = array();
    $rsUsers = CUser::GetList($by, $order, $filter, $arParams);

    while ($user = $rsUsers->Fetch()) {
        print_r($user);
    }
    $res = CTasks::GetList(
        array(),
        array("RESPONSIBLE_ID" => 321, "CREATED_BY" => 45)
    );
    while ($arTask = $res->GetNext()) {
        print_r($arTask['ID']);
        $ID = $arTask['ID'];
        $arFields = array(
            "CREATED_BY" => 321
        );
        $obTask = new CTasks;
        $success = $obTask->Update($ID, $arFields);
        if ($success) {
            echo "Ok!";
        } else {
            if ($e = $APPLICATION->GetException())
                echo "Error: " . $e->GetString();
        }
    }
    /**
     * change no active user
     */
    CModule::IncludeModule("tasks");

    $by = "id";
    $order = "ASC";
    $filter = array("ACTIVE" => "N", "GROUPS_ID" => array(11));
    $arParams = array();
    $rsUsers = CUser::GetList($by, $order, $filter, $arParams);

    while ($user = $rsUsers->Fetch()) {
        $arID[] = $user['ID'];
    }
    print_r($arID);
    foreach ($arID as $id) {
        $results = $DB->Query(
            "SELECT * FROM b_user WHERE b_user.ID={$id}"
        );
        while ($row = $results->Fetch()) {
            print_r($row);
        }
    }
}
