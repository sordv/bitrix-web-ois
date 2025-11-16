<?php

namespace Legacy\API;

use Legacy\HighLoadBlock\Entity;
use Legacy\General\Constants;

class Course {
    public static function create($arRequest) {
        $params = [
            'UF_NAME' => $arRequest['name']
        ];
        $courseId = Entity::getInstance()->add(Constants::HLBLOCK_COURSES, $params);

        $teachers = $arRequest['teachers'];
        foreach ($teachers as $key => $teacherId) {
            self::addTeacherToCourse($courseId, $teacherId);
        }

        $students = $arRequest['students'];
        foreach ($students as $key => $studentId) {
            self::addStudentToCourse($courseId, $studentId);
        }

        return self::getCourseByCode($courseId);
    }

    public static function get($arRequest) {
        $courseId = $arRequest['courseId'];
        if (empty($courseId)) {
            throw new \Exception('Не указан идентификатор курса');
        }

        return self::getCourseByCode($courseId);
    }

    public static function getList($arRequest) {
        $userId = $arRequest['userId'];
        $role = $arRequest['role'];
        if (empty($userId)) {
            throw new \Exception('Не указан идентификатор студента или преподавателя');
        }

        $idField = ($role == "teacher") ? 'UF_TEACHER_ID' : 'UF_STUDENT_ID';
        $hlBlock = ($role == "teacher") ? Constants::HLBLOCK_COURSES_TEACHERS_REL: Constants::HLBLOCK_COURSES_STUDENTS_REL;
        $rels = Entity::getInstance()->getList($hlBlock, [
            'filter' => [
                $idField => $userId
            ]
        ]);

        $processedCourses = [];
        foreach ($rels as $rel) {
            $courseId = $rel['UF_COURSE_ID'];
            $course = self::getCourseByCode($courseId);
            $processedCourses[] = $course;
        }

        return $processedCourses;
    }

    public static function addTeacher($arRequest) {
        $courseId = $arRequest['courseId'];
        if (empty($courseId)) {
            throw new \Exception('Не указан идентификатор курса');
        }

        $teacherId = $arRequest['teacherId'];
        if (empty($teacherId)) {
            throw new \Exception('Не указан идентификатор преподавателя');
        }

        self::addTeacherToCourse($courseId, $teacherId);
    }

    public static function addStudent($arRequest) {
        $courseId = $arRequest['courseId'];
        if (empty($courseId)) {
            throw new \Exception('Не указан идентификатор курса');
        }

        $studentId = $arRequest['studentId'];
        if (empty($studentId)) {
            throw new \Exception('Не указан идентификатор студента');
        }

        self::addStudentToCourse($courseId, $studentId);
    }

    private static function addTeacherToCourse($courseId, $teacherId) {
        $coursesTeachersRelParams = [
            'UF_COURSE_ID' => $courseId,
            'UF_TEACHER_ID' => $teacherId,
        ];

        return Entity::getInstance()->add(Constants::HLBLOCK_COURSES_TEACHERS_REL, $coursesTeachersRelParams);
    }

    private static function addStudentToCourse($courseId, $studentId) {
        $coursesStudentsRelParams = [
            'UF_COURSE_ID' => $courseId,
            'UF_STUDENT_ID' => $studentId,
        ];

        return Entity::getInstance()->add(Constants::HLBLOCK_COURSES_STUDENTS_REL, $coursesStudentsRelParams);
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
        $rels = [];
        foreach ($coursesTeachersRels as $key => $rel) {
            $rels[] = $rel['UF_TEACHER_ID'];
        }
        $coursesTeachersRels = $rels;

        $coursesStudentsRels = Entity::getInstance()->getList(Constants::HLBLOCK_COURSES_STUDENTS_REL, [
            'filter' => [
                'UF_COURSE_ID' => $code
            ]
        ]);
        $rels = [];
        foreach ($coursesStudentsRels as $key => $rel) {
            $rels[] = $rel['UF_STUDENT_ID'];
        }
        $coursesStudentsRels = $rels;

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
}