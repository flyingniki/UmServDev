<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Umserv\Flah\Fields\UserField\Workgroup;
use Bitrix\Main\Component\BaseUfComponent;

/**
 * Class ElementCrmUfComponent
 */
class FlahWorkgroupUfComponent extends BaseUfComponent
{
	protected static function getUserTypeId(): string
	{
		return Workgroup::USER_TYPE_ID;
	}
}
