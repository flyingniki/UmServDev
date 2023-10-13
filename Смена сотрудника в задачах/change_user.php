<?php

if (CModule::IncludeModule("tasks")) {
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
}
