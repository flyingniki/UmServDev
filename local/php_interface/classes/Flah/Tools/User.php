<?php

namespace Umserv\Flah\Tools;

use \Bitrix\Main\UserTable;
use \Bitrix\Main\GroupTable;

class User
{
	/**
	 * Return user group data
	 *
	 * @param integer $userId
	 * @return array
	 */
	public static function getGroups(int $userId): array
	{
		$groups = [];

		$groupIds = UserTable::getUserGroupIds($userId);

		if (empty($groupIds)) {
			return $groups;
		}

		$groupQuery = GroupTable::query()
			->addSelect('ID')
			->addSelect('STRING_ID')
			->whereIn('ID', $groupIds);

		$groupList = $groupQuery->exec();

		foreach ($groupList as $group) {
			$code = !empty($group['STRING_ID'])
				? $group['STRING_ID']
				: 'ID_' . $group['ID'];

			$groups[$code] = [
				'ID'   => $group['ID'],
				'CODE' => $code,
			];
		}

		return $groups;
	}
}
