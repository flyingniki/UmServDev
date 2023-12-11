<?php
namespace Flah\Fields\UserField;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Types\BaseType;

class Workgroup extends BaseType
{
	public const
		USER_TYPE_ID = 'flah_workgroup',
		RENDER_COMPONENT = 'flah:fields.field.workgroup';

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => 'Flah: Workgroup',
			'BASE_TYPE'   => \CUserTypeManager::BASE_TYPE_STRING,
		];
	}

	/**
	 * @param $userField
	 * @return string
	 */
	public static function GetDBColumnType(): string
	{
		return 'varchar(200)';
	}
}