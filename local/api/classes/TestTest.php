<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Legacy\Iblock\IblockElementTable;

class TestTest
{
	public static function getElement($arRequest)
	{
		if (Loader::includeModule('iblock')) { // чтобы работать с элементами модуля, необходимо его подключить, в примере работаем с инфоблоком
			$query = IblockElementTable::query()
				->withSelect()
				->withRuntimeProperties()
				->addFilter('IBLOCK_ID', Constants::IB_TESTTEST)
				->withFilter()
				->withOrder()
				->withPage($arRequest['limit'], $arRequest['page'])
			;
			$count = $query->queryCountTotal(); // количество записей ответа
			$db = $query->exec();

			$result = [];
			while ($res = $db->fetch()) {
				$result[] = $res;
			}
			return [
				'count' => $count,
				'items' => $result
			];
		}
		throw new \Exception('Не удалось подключить необходимые модули');
	}
}