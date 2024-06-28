<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Main;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Order;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Loader;


Loc::loadMessages(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/courierserviceexpress.moduledost/classes/general/KCEClass.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/courierserviceexpress.moduledost/classes/general/KseService.php');
Asset::getInstance()->addJS("/bitrix/js/courierserviceexpress.moduledost/widget.js");

class KCEDeliveryHandler extends Base
{
	protected static $isCalculatePriceImmediately = true;
	protected  static $whetherAdminExtraServicesShow = true;

	public function __construct(array $initParams)
	{
		parent::__construct($initParams);

		//Default value
		if (!isset($this->config["MAIN"]["0"]))
			$this->config["MAIN"]["0"] = "0";
	}

	public static function getClassTitle()
	{
		return Loc::getMessage("KCE_TITLE");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("KCE_DESCRIPTION");
	}

	protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment)
	{

		$result = new CalculationResult;
		if ($shipment->getPrice() > 0) {
			$result->setDeliveryPrice($shipment->getPrice());
			$result->setPeriodFrom($_SESSION['KCE_DATA']['MIN']);
			$result->setPeriodTo($_SESSION['KCE_DATA']['MAX']);
			$result->setPeriodType("D");
			$result->setPeriodDescription($_SESSION['KCE_DATA']['MESS']);
			return $result;
		}
		$temp = new \cKCE();

		//Собираем данные для расчета из настроек модуля        
		$KCElogin = \COption::GetOptionString("courierserviceexpress.moduledost", "login");
		$KCEpass = \COption::GetOptionString("courierserviceexpress.moduledost", "pass");
		//$ZIPfrom = \COption::GetOptionString("courierserviceexpress.moduledost", "sklad-fias");
		$GorodZaboraGruza = \COption::GetOptionString("courierserviceexpress.moduledost", "GorodZaboraGruza");
		$ZIPfrom = \KseService::GetZipCode($GorodZaboraGruza);

		if (!$ZIPfrom) {
			$message = "Connection Error";
			$result->setPeriodDescription($message);
			return $result;
		}

		$Urgency = \COption::GetOptionString("courierserviceexpress.moduledost", "urgency");
		$DeliveryMethodKurier = \COption::GetOptionString("courierserviceexpress.moduledost", "kurierka");
		$DeliveryMethodPVZ = \COption::GetOptionString("courierserviceexpress.moduledost", "pvz");

		//данные по заказу
		$order = $shipment->getCollection()->getOrder(); //заказ
		$props = $order->getPropertyCollection();
		$basket = $order->getBasket();
		$priceBasket = $basket->getPrice();
		$WeightBasket = $basket->GetWeight(); //убрал деление на 1000
		$basketItems = $basket->getBasketItems();

		//Рассчитываем объемный вес заказа
		$objWeight = 0;
		foreach ($basketItems as $basketItem) {
			$quant = $basketItem->getField('QUANTITY');
			$item = \CCatalogProduct::GetByID($basketItem->getField('PRODUCT_ID'));

			$objWeight += round(($item['WIDTH'] / 10 * $item['LENGTH'] / 10 * $item['HEIGHT'] / 10) / 5000 * $quant, 2) * 1000; //добавил * 1000;

			//считаем объемный вес каждого товара в корзине
			$objItemWeight = round(($item['WIDTH'] / 10 * $item['LENGTH'] / 10 * $item['HEIGHT'] / 10) / 5000, 2) * 1000;

			$basketItemWeight = $item['WEIGHT']; //убрал деление на 1000

			if ($objItemWeight > $basketItemWeight * $quant) $basketItemWeight = $objItemWeight;
			//$basketItem->setField('WEIGHT', $basketItemWeight);
			//$basketItem->save();
		}
		if (!$WeightBasket) $WeightBasket = \COption::GetOptionString("courierserviceexpress.moduledost", "massa") * 1000;

		//выбираем наибольший вес для расчета заказа
		if ($objWeight > $WeightBasket) $WeightBasket = $objWeight;

		$ItemsBasket = sizeof($basket->getQuantityList());	//Получаем число SKU в корзине для расчета количества мест

		//$basket = $basket->getList();

		/** @var Order $order */
		$order = $shipment->getCollection()->getOrder();


		if (!$props = $order->getPropertyCollection())
			return $result;

		if (!$locationProp = $props->getDeliveryLocation())
			return $result;
		//AddMessage2Log($locationProp);


		if (!$locationCode = $locationProp->getValue())
			return $result;
		//AddMessage2Log($locationCode);


		$res = \Bitrix\Sale\Location\LocationTable::getList(array(
			'filter' => array('CODE' => $locationCode),
			'select' => array('ID', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
		));

		while ($item = $res->fetch()) {
			$CityID = $item['ID'];
		}

		$arLocs = \CSaleLocation::GetLocationZIP($CityID)->fetch();

		if (!$arLocs['ZIP']) {
			$arLocs = \Bitrix\Sale\Location\ExternalTable::getList(
				array(
					'filter' => array(
						'=LOCATION_ID' => $CityID,
						'=SERVICE.CODE' => 'ZIP_LOWER'
					),
					'select' => array('ID', 'ZIP_LOWER' => 'XML_ID')
				)
			)->fetch();
			$arLocs['ZIP'] = $arLocs['ZIP_LOWER'];
		}

		$ZIPto = $arLocs['ZIP'];

		$arKCE = $temp->GetDeliveryCost($KCElogin, $KCEpass, $ZIPfrom, $ZIPto, $WeightBasket / 1000, $ItemsBasket, $DeliveryMethodKurier, $Urgency);

		$Cost = $arKCE['cost'];

		if ($arKCE['mindays']) {
			$MinDays = $arKCE['mindays'] + \COption::GetOptionString("courierserviceexpress.moduledost", "days-to-sklad") * 1;
			$MaxDays = $arKCE['days'] + \COption::GetOptionString("courierserviceexpress.moduledost", "days-to-sklad") * 1;

			switch (substr($MaxDays, -1)) {
				case "1":
					$daysName = Loc::getMessage("KCE_DAY");
					break;
				case "2":
					$daysName = Loc::getMessage("KCE_DAY2");
					break;
				case "3":
					$daysName = Loc::getMessage("KCE_DAY2");
					break;
				case "4":
					$daysName = Loc::getMessage("KCE_DAY2");
					break;
				case "5":
					$daysName = Loc::getMessage("KCE_DAYS");
					break;
				case "6":
					$daysName = Loc::getMessage("KCE_DAYS");
					break;
				case "7":
					$daysName = Loc::getMessage("KCE_DAYS");
					break;
				case "8":
					$daysName = Loc::getMessage("KCE_DAYS");
					break;
				case "9":
					$daysName = Loc::getMessage("KCE_DAYS");
					break;
			}

			$message = Loc::getMessage("KCE_TIME_TO_DAY") . $MinDays . "-" . $MaxDays . ' ' . $daysName; //Loc::getMessage("KCE_DAYS");
			if ($MinDays == $MaxDays) $message = $MaxDays . ' ' . $daysName; //Loc::getMessage("KCE_DAYS");

		} else {
			$message = Loc::getMessage("KCE_UTOCH");
		}

		//ставим цену

		if ($Cost) {
			//AddMessage2Log($Cost);
			$result->setDeliveryPrice($Cost);
		} else {
			$Cost = \COption::GetOptionString("courierserviceexpress.moduledost", "sum_default");
			$result->setDeliveryPrice($Cost);
		}
		//минимальный срок
		$result->setPeriodFrom($MinDays);

		//максимальный срок
		$result->setPeriodTo($MaxDays);

		//доставка в днях
		$result->setPeriodType("D");

		//текстовое сообщение о доставке
		$result->setPeriodDescription($message);

		$_SESSION['KCE_DATA'] = array('MIN' => $MinDays, 'MAX' => $MaxDays, 'MESS' => $message);

		return $result;
	}

	protected function getConfigStructure()
	{
		return array();

		/*      return array(
            "MAIN" => array(
                "TITLE" => Loc::getMessage("KCE_DOSTTYPE"),
                "DESCRIPTION" => Loc::getMessage("KCE_OBRAB"),
				"ITEMS" => array(
					"DELIVERY_TYPE" => array
					(
						"TYPE" => "ENUM",
						"DEFAULT" => "0",
						"NAME" => Loc::getMessage("KCE_DOSTTYPE"),
						"OPTIONS" => array(
								0 => 'Courier',
								1 => 'PVZ'
						)
					),					
				),
            )
        );
        */
	}

	public function isCalculatePriceImmediately()
	{
		return self::$isCalculatePriceImmediately;
	}

	public static function whetherAdminExtraServicesShow()
	{
		return self::$whetherAdminExtraServicesShow;
	}

	public static function getChildrenClassNames()
	{
		return array(
			//	        'Sale\Handlers\Delivery\KCEDeliveryProfile',
			//            'Sale\Handlers\Delivery\KCEDeliveryProfilePVZ'
		);
	}

	public function getProfilesList()
	{
		return array(Loc::getMessage("KCE_COUR"), Loc::getMessage("KCE_PVZ"));
	}

	public static function getAdminFieldsList()
	{
		$result = parent::getAdminFieldsList();
		$result["STORES"] = true;
		return $result;
	}
}
