<?php

namespace Umserv\Flah\Crm\PaymentRequest;

use \Bitrix\Main,
	\Fusion\Rms,
	\Bitrix\Crm\Item,
	\Bitrix\Crm\Service\Context,
	\Bitrix\Crm\Service\Operation,
	\Umserv\Flah\Crm\PaymentRequest\Operation\ChangeStageRestriction,
	\Bitrix\Crm\Service\Operation\Update,
	\Bitrix\Crm\Service\Factory\Dynamic;

class Factory extends Dynamic
{
	/**
	 * Returns update operations for this entity.
	 *
	 * @param Item $item
	 * @param Context|null $context
	 * @return Update
	 */
	public function getUpdateOperation(Item $item, Context $context = null): Update
	{
		$operation = parent::getUpdateOperation($item, $context);

		$operation
			->addAction(
				Operation::ACTION_BEFORE_SAVE,
				new ChangeStageRestriction()
			);

		return $operation;
	}
}
