<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){die();}

use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\Helper\Path;

if ( !Loader::includeModule('socialnetwork') )
{
	return;
}


if(is_array($arResult['value']) && count($arResult['value'])>0)
{
	$values = [];
	foreach($arResult['value'] as $value)
	{
		$value = intval($value);
		if ( $value > 0 )
		{
			$values[] = $value;
		}
	}

	$arResult['value'] = [];

	if ( count($values) > 0 )
	{
		$groups = WorkgroupTable::getList([
			'select' => ['ID', 'NAME'],
			'filter' => [
				'@ID' => $values
			]
		]);

		foreach ($groups as $group)
		{
			$arResult['value'][] = [
				'ID'   => $group['ID'],
				'NAME' => $group['NAME'],
				'URL'  => str_replace('#group_id#', $group['ID'], Path::get('group_path_template')),
			];
		}
	}
}