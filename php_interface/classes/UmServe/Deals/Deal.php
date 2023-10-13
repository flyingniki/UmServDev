<?php

namespace UmServe\Deals;

class Deal
{
    public static function OnBeforeCrmDealUpdateHandler(&$arFields)
    {
        \CModule::IncludeModule('crm');
        \CModule::IncludeModule('im');
        \CModule::IncludeModule('tasks');
        $arFilter = array(
            "ID" => $arFields["ID"],
            "CHECK_PERMISSIONS" => "N"
        );
        $arSelect = array('ID', 'COMPANY_ID', 'CATEGORY_ID', 'UF_CRM_1694010552', 'UF_CRM_1696705078', 'UF_CRM_1696425191');
        // $res = \CCrmDeal::GetList(array(), $arFilter, $arSelect);
        $res = \CCrmDeal::GetListEx(array(), $arFilter, false, false, $arSelect, array());
        if ($arDeal = $res->Fetch()) {
            /**
             * Restricted if INN field is empty
             */
            if (in_array($arFields['STAGE_ID'], ['C12:EXECUTING', 'C12:FINAL_INVOICE', 'C12:UC_3WSNRP'])) {
                $requisite = new \Bitrix\Crm\EntityRequisite();
                $rs = $requisite->getList([
                    "filter" => [
                        "ENTITY_ID" => $arDeal['COMPANY_ID'], "ENTITY_TYPE_ID" => \CCrmOwnerType::Company,
                    ]
                ]);
                $reqData = $rs->fetchAll();
                if (!$reqData[0]['RQ_INN']) {
                    unset($arFields['STAGE_ID']);
                    $arMessage = array(
                        "MESSAGE" => "Вы не можете изменить стадию сделки без указания ИНН компании",
                        "MESSAGE_TYPE" => 'S',
                        "TO_USER_ID" => $arFields["MODIFY_BY_ID"],
                    );
                    \CIMMessenger::Add($arMessage);
                };
            }
            /**
             * Restricted if type of delivery is not 533
             */
            if ($arFields['STAGE_ID'] == 'C13:PREPARATION') {
                if ($arDeal['UF_CRM_1694010552'] != 533) {
                    unset($arFields['STAGE_ID']);
                    $arMessage = array(
                        "MESSAGE" => "Вы не можете перевести сделку в данную стадию, так как способ доставки от поставщика иной",
                        "MESSAGE_TYPE" => 'S',
                        "TO_USER_ID" => $arFields["MODIFY_BY_ID"],
                    );
                    \CIMMessenger::Add($arMessage);
                };
            }
            /**
             * Restricted if STAGE_ID between NEW and LOSE and task is not completed
             */
            if ($arFields['STAGE_ID'] != 'C12:NEW' || $arFields['STAGE_ID'] != 'C12:LOSE') {
                $rsTask = \CTasks::GetByID($arDeal['UF_CRM_1696705078']);
                if ($arTask = $rsTask->GetNext()) {
                    if (!in_array($arTask['STATUS'], [4, 5])) {
                        unset($arFields['STAGE_ID']);
                        $arMessage = array(
                            "MESSAGE" => "Вы не можете перевести сделку в данную стадию, так как задача [b]{$arTask['TITLE']}[/b] не завершена. Обновите страницу и выполните задачу.",
                            "MESSAGE_TYPE" => 'S',
                            "TO_USER_ID" => $arFields["MODIFY_BY_ID"],
                        );
                        \CIMMessenger::Add($arMessage);
                    }
                }
            }
            /**
             * Restricted if field "Тип доставки" has value "тест клиента" 1898
             */
            if ($arFields['STAGE_ID'] == 'C13:UC_OICNLE') {
                if ($arDeal['UF_CRM_1696425191'] == 1898) {
                    unset($arFields['STAGE_ID']);
                    $arMessage = array(
                        "MESSAGE" => "Вы не можете перевести сделку в данную стадию, так как поле 'Тип доставки' имеет значение 'тест клиента'",
                        "MESSAGE_TYPE" => 'S',
                        "TO_USER_ID" => $arFields["MODIFY_BY_ID"],
                    );
                    \CIMMessenger::Add($arMessage);
                };
            }
            /**
             * Restrict to change CATEGORY_ID
             */
            
        }
    }
}
