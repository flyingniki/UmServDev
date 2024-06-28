<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Page\Asset;

CJSCore::init(['uf']);

$arResult['targetBlank'] = ($arResult['userField']['SETTINGS']['TARGET_BLANK'] ?? 'Y');

foreach ($arResult['value'] as $key => $value) {
	if ($value) {
		$value = (int)$value;
		$tag = '';

		$fileInfo = \CFile::GetFileArray($value);
		if ($fileInfo) {
			//
			$fileInfo['additionals']  = 'data-viewer="null"';
			$fileInfo['additionals'] .= " data-bx-download='/upload/{$fileInfo["SUBDIR"]}/{$fileInfo["FILE_NAME"]}'";
			$fileInfo['additionals'] .= " data-title='" . HtmlFilter::encode($fileInfo['ORIGINAL_NAME']) . "'";
			$fileInfo['additionals'] .= " data-actions='[{\"type\":\"download\"}]'";

			$mimeType = "unknown";

			$fileInfo['additionals'] .= " data-mime-type='" . $fileInfo['CONTENT_TYPE'] . "'";

			if (in_array($fileInfo['CONTENT_TYPE'], [
				"application/vnd.openxmlformats-officedocument.wordprocessingml.document",
				"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
				"application/pdf"
			])) {
				//$mimeType = "cloud-document";
				//$fileInfo['additionals']  = str_replace('data-viewer="null"', 'data-viewer=""', $fileInfo['additionals']);
				//$fileInfo['additionals'] .= ' data-viewer-type-class="BX.Disk.Viewer.OnlyOfficeItem"';
				//$fileInfo['additionals'] .= ' data-viewer-type-class="BX.Disk.Viewer.OnlyOfficeItem"';
				//$fileInfo['additionals'] .= ' data-viewer-separate-item="1"';
				//$fileInfo['additionals'] .= ' data-viewer-extension="disk.viewer.onlyoffice-item"';
				//$fileInfo['additionals'] .= " data-bx-src='/upload/{$fileInfo["SUBDIR"]}/{$fileInfo["FILE_NAME"]}'";
				$mimeType = "document";
			}

			if (in_array($fileInfo['CONTENT_TYPE'], [
				"audio/basic",
				"audio/L24",
				"audio/mp4",
				"audio/aac",
				"audio/mpeg",
				"audio/ogg",
				"audio/vorbis",
				"audio/x",
				"audio/x",
				"audio/vnd",
				"audio/vnd",
				"audio/webm",
			])) {
				$mimeType = "audio";
			}

			if (in_array($fileInfo['CONTENT_TYPE'], [
				'video/mpeg',
				'video/mp4',
				'video/ogg',
				'video/quicktime',
				'video/webm',
				'video/x',
				'video/x',
				'video/x',
				'video/3gpp',
				'video/3gpp2',
			])) {
				$mimeType = "video";
			}
			$fileInfo['additionals'] .= "data-viewer-type='" . $mimeType . "'";
			$arResult['value'][$key] = $fileInfo;
		}
	}
}

/**
 * @var $component FileUfComponent
 */

$component = $this->getComponent();

if ($component->isMobileMode()) {
	Asset::getInstance()->addJs(
		'/bitrix/js/mobile/userfield/mobile_field.js'
	);
	Asset::getInstance()->addJs(
		'/bitrix/components/bitrix/main.field.file/templates/main.view/mobile.js'
	);
}
