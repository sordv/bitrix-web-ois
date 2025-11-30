<?php

namespace Legacy\API;

use Legacy\HighLoadBlock\Entity;
use Legacy\General\Constants;

class Answer {
    public static function create($arRequest) {
        $blockId = $arRequest['blockId'];
        if (empty($blockId)) {
            throw new \Exception('Не указан идентификатор блока');
        }

        $file = $arRequest['file'];
        if (empty($file)) {
            throw new \Exception('Не прикреплён файл');
        }

        $studentId = Auth::getUser()['code'];

        $params = [
            'UF_BLOCK_ID'   => $blockId,
            'UF_FILE'       => $file,
            'UF_STUDENT_ID' => $studentId,
        ];

        $answerId = Entity::getInstance()->add(Constants::HLBLOCK_ANSWERS, $params);

        return self::getAnswerByCode($answerId);
    }

    public static function getByBlock($arRequest) {
        $blockId = $arRequest['blockId'];
        if (empty($blockId)) {
            throw new \Exception('Не указан идентификатор блока');
        }

        $studentId = Auth::getUser()['code'];

        $params = [
            'UF_BLOCK_ID'   => $blockId,
            'UF_STUDENT_ID' => $studentId,
        ];

        $answer = Entity::getInstance()->getRow(Constants::HLBLOCK_ANSWERS, [
            'filter' => $params
        ]);

        return self::mapAnswer($answer);
    }

    public static function get($arRequest) {
        $answerId = $arRequest['answerId'];
        if (empty($answerId)) {
            throw new \Exception('Не указан идентификатор ответа');
        }

        return self::getAnswerByCode($answerId);
    }

    private static function getAnswerByCode($code) {
        $answer = Entity::getInstance()->getRow(Constants::HLBLOCK_ANSWERS, [
            'filter' => [
                'ID' => $code
            ]
        ]);

        return self::mapAnswer($answer);
    }

    private static function mapAnswer($answer) {
        return [
            'code'      => $answer['ID'],
            'blockId'   => $answer['UF_BLOCK_ID'],
            'studentId' => $answer['UF_STUDENT_ID'],
            'file'      => $answer['UF_FILE'],
            'review'    => $answer['UF_REVIEW'],
            'score'     => $answer['UF_SCORE'],
        ];
    }
}