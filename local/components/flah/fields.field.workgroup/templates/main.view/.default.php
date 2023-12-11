<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/**
 * @var array $arResult
 */

Bitrix\Main\UI\Extension::load(['ui.tooltip', 'ui.fonts.opensans']);

Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
if(\CCrmSipHelper::isEnabled())
{
	Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
}

$emptyEntityLabels = $arResult['emptyEntityLabels'];
$publicMode = (isset($arParams['PUBLIC_MODE']) && $arParams['PUBLIC_MODE'] === true);
?>

<?php foreach($arResult['value'] as $arGroup): ?>
<a target="_blank" href="<?= HtmlFilter::encode($arGroup['URL']) ?>">
	<?= HtmlFilter::encode($arGroup['NAME']) ?>
</a>
<?php endforeach; ?>