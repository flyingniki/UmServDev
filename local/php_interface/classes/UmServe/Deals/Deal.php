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
        $arSelect = array('ID', 'COMPANY_ID', 'CATEGORY_ID', 'STAGE_ID', 'UF_CRM_1694010552', 'UF_CRM_1696705078', 'UF_CRM_1696425191', 'UF_CRM_1697191297');
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
             * Restricted if deal is in CATEGORY_ID == 12 && STAGE_ID between NEW and LOSE and task is not completed
             */
            if ($arDeal['CATEGORY_ID'] == 12) {
                if ($arFields['STAGE_ID'] != 'C12:NEW' && $arFields['STAGE_ID'] != 'C12:LOSE') {
                    $obActivities = \CCrmActivity::GetList(
                        $arOrder = array(),
                        $arFilter = array('OWNER_TYPE_ID' => 2, 'OWNER_ID' => $arFields['ID'], 'CHECK_PERMISSIONS' => 'N'),
                        $arGroupBy = false,
                        $arNavStartParams = false,
                        $arSelectFields = array('SETTINGS'),
                        $arOptions = array()
                    );

                    while ($arActivity = $obActivities->Fetch()) {
                        if (isset($arActivity['SETTINGS']['ACTIVITY_STATUS'])) {
                            $arDealAcivity[] = $arActivity['SETTINGS']['ACTIVITY_STATUS'];
                        }
                    }
                    if (isset($arDealAcivity) && (in_array('EXPIRED', $arDealAcivity) || in_array('VIEWED', $arDealAcivity) || in_array('CREATED', $arDealAcivity))) {
                        unset($arFields['STAGE_ID']);
                        $link = "https://crm.umserv.ru/crm/deal/details/{$arFields['ID']}/";
                        $arMessage = array(
                            "MESSAGE" => "Вы не можете перевести [url={$link}]сделку[/url] в данную стадию, так как в ней есть открытые задачи. Обновите страницу и выполните задачи.",
                            "MESSAGE_TYPE" => 'S',
                            "TO_USER_ID" => $arFields["MODIFY_BY_ID"],
                        );
                        \CIMMessenger::Add($arMessage);
                    }
                }
            }

            /**
             * Restricted if STAGE_ID == 'C13:UC_OICNLE'(Реализация) field 'Тип доставки' has value 'тест клиента'(1898)
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
        }
    }
}
