<?php

/**
 * This file store additional requirements for project
 * etc: env data, service locator initialization
 * 
 * @see https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=14032&LESSON_PATH=3913.5062.14032
 */

use \Bitrix\Main,
	\Bitrix\Crm,
	\Fusion\Rms,
	\Bitrix\Main\Config\Option;

/**
 * Env section
 * File moved from DOCUMENT_ROOT directory for security reasons
 */
$envPath = dirname($_SERVER['DOCUMENT_ROOT']);

if ( file_exists($envPath.'/.env') )
{
	$env = Main\Context::getCurrent()->getEnvironment();

	$iniParams = \parse_ini_file($envPath.'/.env', true, INI_SCANNER_TYPED);

	foreach ($iniParams as $key => $value)
	{
		$env->set($key, $value);
	}
	unset($key);
	unset($value);
	unset($iniParams);
}
unset($env);
unset($envPath);

/**
 * Service locator section
 *   if exist
 */
if ( class_exists('\Bitrix\Main\DI\ServiceLocator') )
{
	$serviceLocator = Main\DI\ServiceLocator::getInstance();

	/**
	 * service location naming convention:
	 *     * must contant vendor.
	 *     * must be lowercase
	 *         OK: 'fusion.exchange.service', 'sber.payment.service'
	 *         BAD: 'FUSION_SOME_SERVICE', 'COOL_SERVICE', 'TaSk.SerViCE.i18n'
	 * 
	 * Examples:
	 * 
	 * $serviceLocator->addInstanceLazy('fusion.some.service', [
	 *     'constructor' => static function () use ($serviceLocator) {
	 *         return new \Fusion\SomeModule\Services\SecondService('foo', 'bar');
	 *     }
	 * ]);
	 * 
	 * $serviceLocator->addInstanceLazy('fusion.some.service', [
	 *     'className' => \Fusion\SomeModule\Services\SomeService::class,
	 * ]);
	 * 
	 */

	$paymentEntityTypeId = (int) Option::get("flah.crm", "payment_entity_type_id", 0);
	if ( $paymentEntityTypeId > 0 )
	{
		$serviceLocator->addInstanceLazy(
			"crm.service.factory.dynamic.".$paymentEntityTypeId,
			[
				'constructor' => static function () use ($paymentEntityTypeId) {
					Main\Loader::requireModule('crm');
					$type = Crm\Service\Container::getInstance()
						->getTypeByEntityTypeId($paymentEntityTypeId);
					return new \Flah\Crm\PaymentRequest\Factory($type);
				},
			]
		);
	}
	unset($paymentEntityTypeId);

	unset($serviceLocator);
}