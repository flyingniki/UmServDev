<?
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

require_once ($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/courierserviceexpress.moduledost/classes/general/KCEClass.php');
require_once ($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/courierserviceexpress.moduledost/classes/general/KseService.php');
Asset::getInstance()->addJS("/bitrix/js/courierserviceexpress.moduledost/widget.js");
class KCEDeliveryHandlerPVZ extends Base
{    
	public static function getClassTitle()
	{
		return Loc::getMessage("KCE_TITLE_PVZ");
	}
	
	public static function getClassDescription()
	{
		return Loc::getMessage("KCE_DESCRIPTION_PVZ");
	}
        
    protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment)
    {
			
        $result = new CalculationResult();
        /*if ($shipment->getPrice() > 0) {
            $result->setDeliveryPrice($shipment->getPrice()); 
            $result->setPeriodFrom($_SESSION['KCE_DATA_PVZ']['MIN']);
            $result->setPeriodTo($_SESSION['KCE_DATA_PVZ']['MAX']);
            $result->setPeriodType("D");
            $result->setPeriodDescription($_SESSION['KCE_DATA_PVZ']['MESS']);
            $result->setDescription($_SESSION['KCE_DATA_PVZ']['CODEPVZ']);
            return $result;
        }*/
        $temp = new \cKCE();
        
 		$KCElogin = \COption::GetOptionString("courierserviceexpress.moduledost", "login");
		$KCEpass = \COption::GetOptionString("courierserviceexpress.moduledost", "pass");

		$AddressName = \COption::GetOptionString("courierserviceexpress.moduledost", "inputAddress");
        $GUIDPvz = \COption::GetOptionString("courierserviceexpress.moduledost", "inputPVZFIZ");
        
        $GorodZaboraGruza = \COption::GetOptionString("courierserviceexpress.moduledost", "GorodZaboraGruza");
        $ZIPfrom = \KseService::GetZipCode($GorodZaboraGruza);

if (!$ZIPfrom) { 
	$message = "Connection Error"; 
	$result->setPeriodDescription($message);
	return $result;
}
		$Urgency = \COption::GetOptionString("courierserviceexpress.moduledost", "urgency");
		$DeliveryMethodPVZ = \COption::GetOptionString("courierserviceexpress.moduledost", "pvz");
		
		//данные по заказу
		$order = $shipment->getCollection()->getOrder(); //заказ
        $props = $order->getPropertyCollection(); 
		$basket = $order->getBasket();
		$priceBasket=$basket->getPrice();
		$WeightBasket = $basket -> GetWeight();//убрал деленние на 1000
        $basketItems = $basket -> getBasketItems();
        
        //Рассчитываем объемный вес заказа
        $objWeight = 0;
        foreach ($basketItems as $basketItem) {
            $quant = $basketItem->getField('QUANTITY');
            $item = \CCatalogProduct::GetByID($basketItem->getField('PRODUCT_ID'));

            $objWeight += round(($item['WIDTH']/10 * $item['LENGTH']/10 * $item['HEIGHT']/10) / 5000 * $quant,2)*1000;//добавил * 1000;
            
            //считаем объемный вес каждого товара в корзине
            $objItemWeight = round(($item['WIDTH']/10 * $item['LENGTH']/10 * $item['HEIGHT']/10) / 5000,2)*1000;
            $basketItemWeight = $item['WEIGHT'];//убрал деление на 1000
            if ($objItemWeight > $basketItemWeight*$quant) $basketItemWeight = $objItemWeight;
            //$basketItem->setField('WEIGHT', $basketItemWeight);
            //$basketItem->save();
        }
        
		if (!$WeightBasket) $WeightBasket = \COption::GetOptionString("courierserviceexpress.moduledost", "massa")*1000;
        
        //выбираем наибольший вес для расчета заказа
        if ($objWeight > $WeightBasket) $WeightBasket = $objWeight;
        
        
        
        $ItemsBasket = sizeof($basket -> getQuantityList());	//Получаем число SKU в корзине для расчета количества мест
        
        //$basket = $basket->getList();
        
		$order = $shipment->getCollection()->getOrder();

		if(!$props = $order->getPropertyCollection())
			return $result;

		if(!$locationProp = $props->getDeliveryLocation())
			return $result;

		if(!$locationCode = $locationProp->getValue())
			return $result;
         
         $res = \Bitrix\Sale\Location\LocationTable::getList(array(
            'filter' => array('CODE' => $locationCode),
            'select' => array('ID','NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
            ));
        while($item = $res->fetch())
        {
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
                'select' => array('ID','ZIP_LOWER'=>'XML_ID')
                )
            )->fetch();
            $arLocs['ZIP']=$arLocs['ZIP_LOWER'];
        }   
        
        $ZIPto = $arLocs['ZIP'];
        $cityPostCode = 'postcode-'.$ZIPto;
        $GeoGUID = $temp->GetGeographyID($KCElogin,$KCEpass,$cityPostCode);   
        $PVZToken = \COption::GetOptionString("courierserviceexpress.moduledost", "token");     
        
        $CodePVZ = "<div id='pvzcse'></div><button onclick='csepvzwidget(\"init\", {token: \"$PVZToken\",city:\"$GeoGUID\"}); csepvzwidget(\"open\", {callback: function (params) { console.log(params); document.getElementById(\"soa-property-".$GUIDPvz."\").value = params.guid; document.getElementById(\"soa-property-".$AddressName."\").value = params.geography_name + \", \" + params.name +\", \"+ params.address; alert(\"".Loc::getMessage("KCE_PVZ_DATASEND")."\");document.getElementById(\"pvzcse\").innerHTML =\"".Loc::getMessage("KCE_PVZ_DATASEND")."\"}});return false;'>".Loc::getMessage("KCE_PVZ_VIBOR")."</button>";
        $CodePVZ .= "<style>#soa-property-".$GUIDPvz.", .bx-soa-custom-label[for=soa-property-".$GUIDPvz."]{display:none}</style>";
        $arKCE = $temp ->GetDeliveryCost($KCElogin, $KCEpass, $ZIPfrom, $ZIPto, $WeightBasket/1000, $ItemsBasket, $DeliveryMethodPVZ, $Urgency);
		//$arKCE = $temp ->GetDeliveryCost($KCElogin, $KCEpass, $ZIPfrom, $ZIPto, $WeightBasket, $ItemsBasket, '1', '1', '1', '1');
        $Cost = $arKCE['cost'];		
        
        if ($arKCE['mindays']) {
			$MinDays = $arKCE['mindays'] + \COption::GetOptionString("courierserviceexpress.moduledost", "days-to-sklad")*1;
			$MaxDays = $arKCE['days'] + \COption::GetOptionString("courierserviceexpress.moduledost", "days-to-sklad")*1;
			switch (substr($MaxDays, -1)) {
				case "1": $daysName = Loc::getMessage("KCE_DAY");  break;
				case "2": $daysName = Loc::getMessage("KCE_DAY2");  break;
				case "3": $daysName = Loc::getMessage("KCE_DAY2");  break;
				case "4": $daysName = Loc::getMessage("KCE_DAY2"); break;
				case "5": $daysName = Loc::getMessage("KCE_DAYS"); break;
				case "6": $daysName = Loc::getMessage("KCE_DAYS"); break;
				case "7": $daysName = Loc::getMessage("KCE_DAYS"); break;
				case "8": $daysName = Loc::getMessage("KCE_DAYS"); break;
				case "9": $daysName = Loc::getMessage("KCE_DAYS"); break;
			}
			
			$message = Loc::getMessage("KCE_TIME_TO_DAY").$MinDays."-".$MaxDays.' '.$daysName;//Loc::getMessage("KCE_DAYS");
			if ($MinDays == $MaxDays) $message = $MaxDays.' '.$daysName;//Loc::getMessage("KCE_DAYS");
		} else {
			$message = Loc::getMessage("KCE_UTOCH_PVZ");
		}
		
		//ставим цену
		if ($Cost) {
			$result->setDeliveryPrice($Cost);	
		} else {
			$result->setDeliveryPrice(Loc::getMessage("KCE_UTOCH_PVZ"));	
		}
		//минимальный срок
        $result->setPeriodFrom($MinDays);
		
		//максимальный срок
        $result->setPeriodTo($MaxDays);
		
		//доставка в днях
		$result->setPeriodType("D");
		
		//текстовое сообщение о доставке
        $result->setPeriodDescription($message);
        
        $result->setDescription($CodePVZ);
        
        $_SESSION['KCE_DATA_PVZ'] = array ('MIN'=>$MinDays,'MAX'=>$MaxDays, 'MESS'=>$message, 'CODEPVZ'=>$codePVZ);
        
		return $result;
		
    }
        
    protected function getConfigStructure() 
    {
	   return array();
       
        /*return array(
            "MAIN" => array(
                "TITLE" =>Loc::getMessage("KCE_CODE_PVZ"),
                "DESCRIPTION" =>Loc::getMessage("KCE_CODE_PVZ"),
				"ITEMS" => array(
					"WPVZ" => array
					(
						"TYPE" => "STRING",
                        			"SIZE" => 60,
						"DEFAULT" => "",
						"NAME" => Loc::getMessage("KCE_CODE_PVZ")
					),					
				),
            )
        );*/
        
		
    }
        
    public function isCalculatePriceImmediately()
    {
        return true;
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
	    //return array("Курьерская доставка службой Курьер Сервис Экспресс", "Доставка в ПВЗ службой Курьер Сервис Экспресс");
        return array();
	}
        
    public static function whetherAdminExtraServicesShow()
    {
        return true;
    }
	public static function getAdminFieldsList()
	{
		$result = parent::getAdminFieldsList();
		$result["STORES"] = true;
		return $result;
	}
	
}
?>