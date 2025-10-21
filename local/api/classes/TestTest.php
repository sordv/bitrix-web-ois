<?php

namespace Legacy\API;

use Legacy\General\Constants;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;

class TestTest
{
	public static function get($arRequest)
	{
		Loader::includeModule('iblock');

		$params = [
			'select' => ['*'],
			'filter' => ['IBLOCK_ID' => Constants::IB_TESTTEST],
		];

		$items = ElementTable::getList($params)->fetchAll();

		return $items;
	}
}