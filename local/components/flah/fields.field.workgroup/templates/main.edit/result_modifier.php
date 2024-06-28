<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Settings\LayoutSettings;
use Bitrix\Crm\UserField\Types\ElementType;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Crm\UserField\DataModifiers;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\UI\EntitySelector\Dialog;

if (!Loader::includeModule('socialnetwork')) {
	return;
}

$component = $this->getComponent();

if (!$component->isDefaultMode()) {
	return;
}

CUtil::InitJSCore(['ajax', 'popup']);
\Bitrix\Main\UI\Extension::load(['sidepanel', 'ui.entity-selector']);

$settings = $arParams['userField']['SETTINGS'];
$arResult['MULTIPLE'] = $arParams['userField']['MULTIPLE'];

$arResult['SELECTED_LIST'] = [];

$selectorEntityTypes = [];


if (!is_array($arResult['value'])) {
	$arResult['value'] = explode(';', $arResult['value']);
} else {
	$values = [];
	foreach ($arResult['value'] as $value) {
		foreach (explode(';', $value) as $val) {
			if (!empty($val)) {
				$values[$val] = $val;
			}
		}
	}
	$arResult['value'] = $values;
}

$preselectedItems = [];

foreach ($arResult['value'] as $value) {
	if (empty($value)) continue;

	$preselectedItems[] = ['project', $value];
}

$arResult['PRESELECTED_ITEMS'] = Dialog::getItems($preselectedItems)->jsonSerialize();
