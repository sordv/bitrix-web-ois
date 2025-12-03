<?php

namespace Legacy\API;

use Legacy\HighLoadBlock\Entity;
use Legacy\General\Constants;

class Course {
    public static function get($arRequest) {
        $courseId = $arRequest['courseId'];
        if (empty($courseId)) {
            throw new \Exception('Не указан идентификатор курса');
        }

        return self::getCourseByCode($courseId);
    }

    public static function search($arRequest) {
        $studentId = Auth::getUser()['code'];

        $name = trim($arRequest['name'] ?? '');

        $rels = Entity::getInstance()->getList(Constants::HLBLOCK_COURSES_STUDENTS_REL, [
            'filter' => ['UF_STUDENT_ID' => $studentId]
        ]);

        $studentIds = self::mapRels($rels, 'UF_COURSE_ID');
        if (!$studentIds) {
            return [];
        }

        $params = [
            'ID' => $studentIds
        ];

        if ($name !== '') {
            $params['%UF_NAME'] = $name;
        }

        $courses = Entity::getInstance()->getList(Constants::HLBLOCK_COURSES, [
            'filter' => $params
        ]);

        $processedCourses = [];
        foreach ($courses as $course) {
            $course = self::getCourseByCode($course['ID']);
            $processedCourses[] = $course;
        }

        return $processedCourses;
    }

    private static function getCourseByCode($code)
    {
        $course = Entity::getInstance()->getRow(Constants::HLBLOCK_COURSES, [
            'filter' => [
                'ID' => $code
            ]
        ]);

        $coursesTeachersRels = Entity::getInstance()->getList(Constants::HLBLOCK_COURSES_TEACHERS_REL, [
            'filter' => [
                'UF_COURSE_ID' => $code
            ]
        ]);
        $coursesTeachersRels = self::mapRels($coursesTeachersRels, 'UF_TEACHER_ID');

        $coursesStudentsRels = Entity::getInstance()->getList(Constants::HLBLOCK_COURSES_STUDENTS_REL, [
            'filter' => [
                'UF_COURSE_ID' => $code
            ]
        ]);
        $coursesStudentsRels = self::mapRels($coursesStudentsRels, 'UF_STUDENT_ID');

        return self::mapCourse($course, $coursesTeachersRels, $coursesStudentsRels);
    }

    private static function mapCourse($course, $coursesTeachersRels, $coursesStudentsRels) {
        return [
            'code' => $course['ID'],
            'name' => $course['UF_NAME'],
            'teachers' => $coursesTeachersRels,
            'students' => $coursesStudentsRels
        ];
    }

    private static function mapRels($rels, $field) {
        return array_column($rels, $field);
    }
}