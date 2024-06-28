<?php

namespace Umserv\Umsoft\Contacts;

class Contact
{
    public static function OnBeforeCrmContactDeleteHandler($contactId)
    {
        global $DB;

        \CModule::IncludeModule('crm');

        $rsMulti = \CCrmFieldMulti::GetList(
            [],
            [
                'ENTITY_ID' => 'CONTACT',
                'ELEMENT_ID' => $contactId
            ]
        );

        while ($arMulti = $rsMulti->fetch()) {
            if ($arMulti['TYPE_ID'] == 'PHONE') {
                $arPhoneValues[] = $arMulti['VALUE'];
            }
        }

        foreach ($arPhoneValues as $value) {
            $value = substr(preg_replace('/[^0-9]/', '', $value), 1);
            $dbDel = $DB->query(
                "UPDATE b_crm_deal 
                SET SEARCH_CONTENT = REPLACE(SEARCH_CONTENT, $value, 'X') 
                WHERE SEARCH_CONTENT LIKE '%$value%'"
            );
        }

        while ($arMulti = $rsMulti->fetch()) {
            $multi = new \CCrmFieldMulti();
            $clearResult = $multi->Delete($arMulti['ID'], $options = null);
        }
    }
}
