<?php

CModule::IncludeModule("tasks");

$filter = array(
    "ACTIVE" => "N",
);
$rsUsers = CUser::GetList(($by = "active"), ($order = "desc"), $filter);
while ($arUsers = $rsUsers->GetNext()) {
    // print_r($arUsers['ID']);

    $resTasks = CTasks::GetList(
        array("TITLE" => "ASC"),
        array("CREATED_BY" => $arUsers['ID'], "RESPONSIBLE_ID" => 8)
    );
    while ($arTask = $resTasks->GetNext()) {
        print_r($arTask);
        $arFields = array(
            "CREATED_BY" => $arTask['RESPONSIBLE_ID']
        );
        // $obTask = new CTasks;
        // $success = $obTask->Update($arTask['ID'], $arFields);
        if ($success) {
            echo "Ok!";
        } else {
            if ($e = $APPLICATION->GetException())
                echo "Error: " . $e->GetString();
        }
    }
}
