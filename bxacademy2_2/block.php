<?php
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

Loader::includeModule('highloadblock');

$hlblockId = 2; // ваш ID highload-блока

// Получаем описание HL-блока
$hlblock = HighloadBlockTable::getById($hlblockId)->fetch();
$entity = HighloadBlockTable::compileEntity($hlblock);
$entityDataClass = $entity->getDataClass();

// Получаем все записи
$rsData = $entityDataClass::getList([
    'select' => ['*'],
    'order' => ['ID' => 'ASC'],
]);

echo "<ul>";
while ($arItem = $rsData->fetch()) {
    echo "<li>" . htmlspecialchars($arItem['UF_COURSE_ID']) . "</li>";
}
echo "</ul>";
