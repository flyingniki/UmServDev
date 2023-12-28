<?php

CModule::IncludeModule('crm');
CModule::IncludeModule("workflow");
CModule::IncludeModule("bizproc");

$lead = CCrmLead::GetByID('{{ID}}');

$departmentId = 720; // МПП
$arSelect = ['ID'];
$arEmployees = CIntranetUtils::GetDepartmentEmployees($departmentId, false, false, 'Y', $arSelect);

$departmentId = 314; // РОП
$managerId = CIntranetUtils::GetDepartmentManagerID($departmentId);

while ($rsEmployees = $arEmployees->fetch()) {
    $arEmp[] = $rsEmployees['ID'];
}

/**
 * add managerId to arEmp
 */
$arEmp[] = $managerId;

/**
 * if incoming call && in_array($lead['ASSIGNED_BY_ID'], $arEmp)
 */
if (in_array($lead['SOURCE_ID'], [1, 2, 6]) && in_array($lead['ASSIGNED_BY_ID'], $arEmp)) {
    $dealCreateBy = $lead['ASSIGNED_BY_ID'];

    /**
     * get lead phone number
     */
    $dbResMultiFields = CCrmFieldMulti::GetList(
        array('ID' => 'asc'),
        array('ENTITY_ID' => 'LEAD', 'ELEMENT_ID' => '{{ID}}', 'TYPE_ID' => 'PHONE')
    );
    $resMultiFields = $dbResMultiFields->fetch();
    $phone = preg_replace('/[^0-9]/', '', $resMultiFields['VALUE']);

    /**
     * get other lead's contacts with same phone
     */
    if (isset($phone)) {
        $dbResMultiFields = CCrmFieldMulti::GetList(
            array('ID' => 'asc'),
            array('ENTITY_ID' => 'LEAD', 'VALUE' => $phone, 'TYPE_ID' => 'PHONE')
        );
        while ($resMultiFields = $dbResMultiFields->fetch()) {
            if ($resMultiFields['ELEMENT_ID'] != '{{ID}}') {
                $arOtherLeadIds[] = $resMultiFields['ELEMENT_ID'];
            }
        }

        if (empty($arOtherLeadIds)) {
            // $leadCheck = 'Current lead\'s phone number is unique. Start to check open deals';
            $leadCheck = 'Номер телефона из лида уникальный. Начинаем проверку открытых сделок';
            // echo $leadCheck;

            /**
             * get open deal list
             */
            $arFilter = array(
                'CLOSED' => 'N',
                'CATEGORY_ID' => 12,
                'CHECK_PERMISSIONS' => 'N'
            );
            $arSelect = array();
            $res = CCrmDeal::GetListEx(array('DATE_CREATE' => 'ASC'), $arFilter, false, false, $arSelect, array());
            while ($row = $res->GetNext()) {
                if ($row['CONTACT_ID']) {
                    $arContactId[] = $row['CONTACT_ID'];
                }
            }

            foreach ($arContactId as $contactId) {
                $dbResMultiFields = CCrmFieldMulti::GetList(
                    array('ID' => 'asc'),
                    array('ENTITY_ID' => 'CONTACT', 'ELEMENT_ID' => $contactId, 'TYPE_ID' => 'PHONE')
                );
                while ($resMultiFields = $dbResMultiFields->GetNext()) {
                    $arContactPhones[] = preg_replace('/[^0-9]/', '', $resMultiFields['VALUE']);
                }
            }

            if (!in_array($phone, $arContactPhones)) {
                // echo 'convert lead';
                // $convert = 'Phone is not in contacts. Start to convert lead';
                $convert = 'Телефон не найден в контактах CRM. Начало конвертации лида в сделку';

                /**
                 * true, если нужно проверять права текущего пользователя.
                 * Текущий пользователь определяется ID в ключе CURRENT_USER
                 * $arOptions
                 * @var boolean
                 */
                $bCheckRight = false;

                /**
                 * Добавляемая сущность
                 * @var array
                 */
                $dealFields = [
                    'TITLE'    => $lead['TITLE'],
                    'CREATED_BY_ID' => $lead['ASSIGNED_BY_ID'],
                    'CATEGORY_ID' => 12,
                    'STAGE_ID' => 'C12:NEW',
                    'TYPE_ID' => 'SALE',
                    'LEAD_ID' => $lead['ID'],
                    'OPENED' => 'Y',
                    'SOURCE_ID' => $lead['SOURCE_ID'],
                    'SOURCE_DESCRIPTION' => $lead['SOURCE_DESCRIPTION'],
                ];

                $dealObject = new \CCrmDeal($bCheckRight);

                $dealId = $dealObject->Add(
                    $dealFields,
                    $bUpdateSearch = true,
                    $arOptions = [

                        /**
                         * В случае если флаг true не будут проверяться:
                         * - Пользовательские обязательные поля
                         * - Валидация пользовательских полей
                         * @var boolean
                         */
                        'DISABLE_USER_FIELD_CHECK' => true,

                        /**
                         * В случае если флаг true, не будет проверки
                         * обязательности заполнения пользовательских полей
                         * 
                         * Если флаг `DISABLE_USER_FIELD_CHECK` установлен в true,
                         * данный флаг игнорируется - проверок не будет
                         * 
                         * Флаг не отменяет необходимость корректного заполнения
                         * переданных полей.
                         * @var boolean
                         */
                        'DISABLE_REQUIRED_USER_FIELD_CHECK' => true,
                    ]
                );

                if ($dealId) {
                    $this->SetVariable('deal_id', $dealId);

                    $workflowTemplateId = 286;
                    $arErrorsTmp = array();
                    CBPDocument::StartWorkflow(
                        $workflowTemplateId,
                        array("crm", "CCrmDocumentDeal", "DEAL_" . $dealId),
                        array(),
                        $arErrorsTmp
                    );

                    /**
                     * update lead
                     */

                    /**
                     * true, если нужно проверять права текущего пользователя.
                     * Текущий пользователь определяется ID в ключе CURRENT_USER
                     * $arOptions
                     * @var boolean
                     */
                    $bCheckRight = false;

                    /**
                     * Идентификато изменяемого лида
                     * @var integer
                     */
                    $leadId = $lead['ID'];

                    /**
                     * Поля изменяемого лида
                     * @var array
                     */
                    $leadFields = [
                        'STATUS_ID' => 'CONVERTED',
                        'STATUS_SEMANTIC_ID' => 'S'
                    ];

                    $leadEntity = new \CCrmLead($bCheckRight);

                    $isUpdateSuccess = $leadEntity->Update(
                        $leadId,
                        $leadFields,
                        $bCompare = true,
                        $arOptions = [

                            /**
                             * Флаг системного действия. В случае true в лиде не будут
                             * занесены данные о пользователе который производит действие
                             * и дата изменения лида не изменится.
                             * @var boolean
                             */
                            'IS_SYSTEM_ACTION' => false,

                            /**
                             * Флаг отвечающий на необходимость обработки событий.
                             * В случае false события не будут вызваны
                             * @var boolean
                             */
                            'ENABLE_SYSTEM_EVENTS' => true,

                            /**
                             * Флаг синхронизации семантической стадии сделки.
                             * При false битрикс не будет проверять изменилась ли семантика стадии
                             * 
                             * Рекомендуется всегда использовать значение по-умолчанию.
                             * @var boolean
                             */
                            'SYNCHRONIZE_STATUS_SEMANTICS' => true,

                            /**
                             * В случае если флаг true битрикс запросит конфигурацию CRM необходимо ли завершать дела при завершении сущности.
                             * @var boolean
                             */
                            'ENABLE_ACTIVITY_COMPLETION' => true,

                            /**
                             * В случае true, битрикс создаст сообщение в ленту о новом лиде
                             * @var boolean
                             */
                            'REGISTER_SONET_EVENT' => false,

                            /**
                             * В случае если флаг true при добавлении лида не будут проверяться:
                             * - Поля обязательные со стадии
                             * - Валидация пользовательских полей
                             * @var boolean
                             */
                            'DISABLE_USER_FIELD_CHECK' => true,

                            /**
                             * В случае если флаг имеет значение true, а флаг 
                             * DISABLE_USER_FIELD_CHECK не определен или false
                             * не будет проверять обязательность полей со стадии, но 
                             * не отменяет валидацию пользовательских полей
                             */
                            'DISABLE_REQUIRED_USER_FIELD_CHECK' => true,
                        ]
                    );

                    if ($isUpdateSuccess) {
                        $this->SetVariable('leadUpdate', $isUpdateSuccess);
                    } else {
                        /**
                         * Произошла ошибка при обновлении лида, посмотреть ее можно
                         * через любой из способов ниже:
                         * 1. $leadFields['RESULT_MESSAGE']
                         * 2. $leadEntity->LAST_ERROR
                         */
                        $this->SetVariable('error', $leadEntity->LAST_ERROR);
                    }
                } else {
                    $this->SetVariable('error', $dealObject->LAST_ERROR);
                }
            } else {
                // echo "don't convert lead";
                // $convert = 'Found same phone number. Don\'t convert lead';
                $convert = 'Найден номер в контактах CRM. Не конвертируем лид';
            }
            $this->SetVariable('convert', $convert);
        } else {
            // $leadCheck = 'Found leads with same phone. Don\'t convert lead';
            $leadCheck = 'Найдены сделки с таким же номеров телефона. Не конвертируем лид';
            // echo $leadCheck;
        }
    } else {
        // echo "don't convert lead";
        // $leadCheck = 'Lead\'s phone is empty. Don\'t convert lead';
        $leadCheck = 'Номер телефона лида пустой. Не конвертируем лид';
    }
} else {
    // echo "don't convert lead";
    // $leadCheck = 'Another source of lead or lead assigned from another department. Don\'t convert lead';
    $leadCheck = 'Иной источник лида либо ответственный за лид из другого подразделения. Не конвертируем лид';
}

$this->SetVariable('source_id', $lead['SOURCE_ID']);
$this->SetVariable('lead_assigned_id', $lead['ASSIGNED_BY_ID']);
$this->SetVariable('leadCheck', $leadCheck);
