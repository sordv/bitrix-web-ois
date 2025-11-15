<?php

namespace Legacy\API;

use Legacy\HighLoadBlock\Entity;
use Legacy\General\Constants;

class Block {
    public static function create($arRequest) {
        // Все ли поля должны быть заполнены? Какая проверка нужна?
        $params = [
            'UF_NAME' => $arRequest['name'],
            'UF_DESCRIPTION' => $arRequest['description'],
            'UF_COURSE_ID' => $arRequest['courseId'],
            'UF_SORT_ORDER' => $arRequest['sortOrder'],
            'UF_FILE' => $arRequest['file'],
            'UF_TYPE' => $arRequest['type'],
            'UF_DATASTART' => $arRequest['dataStart'],
            'UF_DATAEND' => $arRequest['dataEnd'],
            'UF_MAXSCORE' => $arRequest['maxScore'],
        ];

        $blockId = Entity::getInstance()->add(Constants::HLBLOCK_EDUCATIONAL_BLOCK, $params);

        return self::getBlockById($blockId);
    }

    public static function getList($arRequest) {
        $courseId = $arRequest['courseId'];

        $params = [
            'filter' => [
                'UF_COURSE_ID' => $courseId
            ]
        ];
        $blocks = Entity::getInstance()->getList(Constants::HLBLOCK_EDUCATIONAL_BLOCK, $params);

        $processedBlocks = [];
        foreach ($blocks as $key => $block) {
            $processedBlocks[] = self::mapBlock($block);
        }

        return $processedBlocks;
    }

    public static function update($arRequest) {
        $blockId = (int)$arRequest['blockId'];
        if (empty($blockId)) {
            throw new \Exception('Не указан идентификатор блока');
        }

        $oldBlock = self::getBlockById($blockId);
        if (!$oldBlock) {
            throw new \Exception('Блок не найден');
        }

        $params = [
            'UF_NAME'        => $arRequest['name']        ?? $oldBlock['name'],
            'UF_DESCRIPTION' => $arRequest['description'] ?? $oldBlock['description'],
            'UF_COURSE_ID'   => $arRequest['courseId']    ?? $oldBlock['courseId'],
            'UF_SORT_ORDER'  => $arRequest['sortOrder']   ?? $oldBlock['sortOrder'],
            'UF_FILE'        => $arRequest['file']        ?? $oldBlock['file'],
            'UF_TYPE'        => $arRequest['type']        ?? $oldBlock['type'],
            'UF_DATASTART'   => $arRequest['dataStart']   ?? $oldBlock['dataStart'],
            'UF_DATAEND'     => $arRequest['dataEnd']     ?? $oldBlock['dataEnd'],
            'UF_MAXSCORE'    => $arRequest['maxScore']    ?? $oldBlock['maxScore'],
        ];

        Entity::getInstance()->update(Constants::HLBLOCK_EDUCATIONAL_BLOCK, $blockId, $params);

        return self::getBlockById($blockId);
    }

    public static function delete($arRequest) {
        $blockId = (int)$arRequest['blockId'];
        if (empty($blockId)) {
            throw new \Exception('Не указан идентификатор блока');
        }

        $deletingBlock = self::getBlockById($blockId);
        if (!$deletingBlock) {
            throw new \Exception('Блок не найден');
        }

        Entity::getInstance()->delete(Constants::HLBLOCK_EDUCATIONAL_BLOCK, $blockId);
    }

    private static function getBlockById($id)
    {
        $block = Entity::getInstance()->getRow(Constants::HLBLOCK_EDUCATIONAL_BLOCK, [
            'filter' => [
                'ID' => $id
            ]
        ]);

        return self::mapBlock($block);
    }

    private static function mapBlock($block) {
        return [
            'code' => $block['ID'],
            'name' => $block['UF_NAME'],
            'description' => $block['UF_DESCRIPTION'],
            'courseId' => $block['UF_COURSE_ID'],
            'sortOrder' => $block['UF_SORT_ORDER'],
            'file' => $block['UF_FILE'],
            'type' => $block['UF_TYPE'],
            'dataStart' => $block['UF_DATASTART'],
            'dataEnd' => $block['UF_DATAEND'],
            'maxScore' => $block['UF_MAXSCORE'],
        ];
    }
}