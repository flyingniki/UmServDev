<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

\CModule::IncludeModule('tasks');
\CModule::IncludeModule('crm');

require_once('./department_report.php');
require_once('./deals_report.php');
