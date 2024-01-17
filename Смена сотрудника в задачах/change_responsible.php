<?php

if (CModule::IncludeModule("tasks")) {
    $res = CTasks::GetList(
        array("ID" => "DESC"),
        array("RESPONSIBLE_ID" => "274", "<=DEADLINE" => "31.12.2023"),
        array("ID", "RESPONSIBLE_ID", "DEADLINE")
    );
    while ($arTask = $res->Fetch()) {
        // print_r($arTask);
        $oTaskItem = new CTaskItem($arTask['ID'], 274);
        try {
            $rs = $oTaskItem->Update(array("STATUS" => 5));
        } catch (Exception $e) {
            print('Error');
        }
    }
}
