<?php

namespace Umserv\Flah\Crm\PaymentRequest\Operation;

use Bitrix\Crm\Item;
use Umserv\Flah\Tools\User;
use Bitrix\Main\Result;
use Bitrix\Crm\Service\Operation\Action;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;

class ChangeStageRestriction extends Action
{
	public function process(Item $item): Result
	{
		$actionResult = new Result();

		if (!$item->isChanged(Item::FIELD_NAME_STAGE_ID)) {
			return $actionResult;
		}

		$actionUserId = $this->getContext()->getUserId();

		if (empty($actionUserId)) {
			return $actionResult;
		}

		$userGroup = User::getGroups($actionUserId);

		if (
			array_key_exists('ID_1', $userGroup)
			|| array_key_exists('ACCOUNTING', $userGroup)
		) {
			return $actionResult;
		}

		$actionResult->addError(
			new Error("Только сотрудники группы 'Бухгалтерия заявок' могут изменять статус")
		);

		return $actionResult;
	}
}
