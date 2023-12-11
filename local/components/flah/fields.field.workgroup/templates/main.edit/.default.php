<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Crm\UserField\Types\ElementType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Web\Json;

$component = $this->getComponent();

$fieldName = $arParams['userField']['FIELD_NAME'];
$formName = (isset($arParams['form_name']) ? (string)$arParams['form_name'] : '');

$randString = $this->randString();
if ($component->isAjaxRequest())
{
	$randString .= time();
}

$fieldUID = mb_strtolower(str_replace('_', '-', $fieldName)) . $randString;
if($formName !== '')
{
	$fieldUID = mb_strtolower(str_replace('_', '-', $formName)).'-' . $fieldUID;
}
$fieldUID = CUtil::JSescape($fieldUID);

$jsObject = 'FlahWorkgroupSelector_' . $randString;

$tagSelectorDivIdentifier = "flah-{$fieldUID}-div";
$inputNodesDivIdentifier = "flah-{$fieldUID}-values";

$fieldName = HtmlFilter::encode($fieldName . ($arResult['MULTIPLE'] === 'Y' ? '[]' : ''));
?>
<div id="flah-<?=$fieldUID;?>-box" class='fields flah_workgroup field-wrap'>

	<input
			type="hidden"
			name="<?=$fieldName?>"
			value=""
			id="<?= $arResult['userField']['FIELD_NAME'] ?>_default"
		>
	
	<div id='<?=$inputNodesDivIdentifier;?>'>
		<input type="hidden" name="<?=$fieldName?>" value="">
		<? foreach($arResult['value'] as $value): ?>
			<? $value = HtmlFilter::encode($value); ?>
			<input type="hidden" name="<?=$fieldName?>" value="<?=$value?>">
		<? endforeach; ?>
	</div>

	<div id="<?=$tagSelectorDivIdentifier;?>"></div>
	
	<script>
	BX.ready(function(){
		const tagSelector = new BX.UI.EntitySelector.TagSelector({
			id: "<?=$tagSelectorDivIdentifier;?>",
			multiple: <?=$arResult['MULTIPLE']=='Y'? 'true' : 'false';?>,
			items: <?=Json::encode($arResult['PRESELECTED_ITEMS']);?>,
			dialogOptions: {
				context: "<?=$arResult["additionalParameters"]['CONTEXT'];?>",
				entities: [
					{
						id: "project",
						dynamicLoad: true,
						dynamicSearch: true,
						options: {
							myProjectsOnly: false,
							fillRecentTab: true
						}
					}
				],
				events: {
					'Item:onSelect': function (event) {
						BX.append(BX.create('input',{
							attrs: {
								type: "hidden",
								name: "<?=$fieldName;?>",
								value: event.getData().item.id
							}
						}), document.getElementById("<?=$inputNodesDivIdentifier;?>"));

						BX.fireEvent(BX("<?= $arResult['userField']['FIELD_NAME'] ?>_default"), 'change');
					},
					'Item:onDeselect': function (event) {
						let itemNode = document.getElementById("<?=$inputNodesDivIdentifier;?>").querySelector("input[value='"+event.getData().item.id+"']");
						
						if ( BX.Type.isDomNode(itemNode) )
						{
							BX.cleanNode(itemNode, true);
						}

						BX.fireEvent(BX("<?= $arResult['userField']['FIELD_NAME'] ?>_default"), 'change');
					}
				}
			},
		});
		tagSelector.renderTo(document.getElementById("<?=$tagSelectorDivIdentifier;?>"))
	});
	</script>
</div>