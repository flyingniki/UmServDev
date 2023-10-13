<?

namespace Sale\Handlers\Delivery;

use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Main\Localization\Loc;


class KCEDeliveryProfile extends Base
{
    protected static $isProfile = true;
    protected static $parent = null;

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
        $this->parent = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($this->parentId);
    }

    public static function getClassTitle()
    {
        return 'KCE Delivery profile';
    }

    public static function getClassDescription()
    {
        return 'My custom handler for KCE Delivery Service profile';
    }

    public function getParentService()
    {
        return $this->parent;
    }

    public function isCalculatePriceImmediately()
    {
        return $this->getParentService()->isCalculatePriceImmediately();
    }

    public static function isProfile()
    {
        return self::$isProfile;
    }

	protected function getConfigStructure()
	{
/*	    $result = array(
	        "MAIN" => array(
	            'TITLE' => 'Основные',
	            'DESCRIPTION' => 'Основные настройки',
	            'ITEMS' => array(
	                'TARIFF_ID' => array(
	                    "TYPE" => 'STRING',
	                    "NAME" => 'ID тарифа службы доставки',
	                ),
	            )
	        )
	    );
	    return $result;*/
	}

	protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment = null)
	{
	    // Какие-то действия по получению стоимости и срока...
        
        		//данные по заказу
		$order = $shipment->getCollection()->getOrder(); //заказ
        $props = $order->getPropertyCollection(); 
		$basket = $order->getBasket();
		$priceBasket=$basket->getPrice();
        
        //$basket = $basket->getList();
        
        $locationCode = $props->getDeliveryLocation()->getValue(); // местоположение
        
        pr ($locationCode);
        pr ("123123123123123213");
	
	    $result = new \Bitrix\Sale\Delivery\CalculationResult();
	    $result->setDeliveryPrice(
	        roundEx(
	            500,
	            SALE_VALUE_PRECISION
	        )
	    );
	    $result->setPeriodDescription('2-3 days');
	
	    return $result;
	}

	public function isCompatible(\Bitrix\Sale\Shipment $shipment)
	{
	    $calcResult = self::calculateConcrete($shipment);
	    return $calcResult->isSuccess();
	}
}