<?php

namespace Legacy\API;

use Legacy\HighLoadBlock\Entity;
use Legacy\General\Constants;

class Block {
    public static function get($arRequest) {
        $blockId = $arRequest['blockId'];
        if (empty($blockId)) {
            throw new \Exception('Не указан идентификатор блока');
        }

        return self::getBlockByCode($blockId);
    }

    public static function search($arRequest) {
        $courseId = $arRequest['courseId'];
        if (empty($courseId)) {
            throw new \Exception('Не указан идентификатор курса');
        }

        $name = trim($arRequest['name'] ?? '');

        $params = ['UF_COURSE_ID' => $courseId];

        if ($name !== '') {
            $params['%UF_NAME'] = $name;
        }

        $blocks = Entity::getInstance()->getList(Constants::HLBLOCK_BLOCKS, [
            'filter' => $params
        ]);

        $processedBlocks = [];
        foreach ($blocks as $block) {
            $processedBlocks[] = self::getBlockByCode($block['ID']);
        }

        return $processedBlocks;
    }

    private static function getBlockByCode($code)
    {
        $block = Entity::getInstance()->getRow(Constants::HLBLOCK_BLOCKS, [
            'filter' => [
                'ID' => $code
            ]
        ]);

        return self::mapBlock($block);
    }

    private static function mapBlock($block) {
        return [
            'code'        => $block['ID'],
            'name'        => $block['UF_NAME'],
            'description' => $block['UF_DESCRIPTION'],
            'courseId'    => $block['UF_COURSE_ID'],
            'sortOrder'   => $block['UF_SORT_ORDER'],
            'file'        => $block['UF_FILE'],
            'type'        => $block['UF_TYPE'],
            'dataStart'   => $block['UF_DATASTART'],
            'dataEnd'     => $block['UF_DATAEND'],
            'maxScore'    => $block['UF_MAXSCORE'],
        ];
    }
}