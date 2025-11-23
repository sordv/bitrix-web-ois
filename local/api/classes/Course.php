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
        if (count($teachers) == 0) {
            throw new \Exception('Не указаны идентификаторы преподавателей');
        }

        foreach ($teachers as $teacherId) {
            self::addTeacherToCourse($courseId, $teacherId);
        }

        $students = $arRequest['students'];
        if (count($students) == 0) {
            throw new \Exception('Не указаны идентификаторы студентов');
        }

        foreach ($students as $studentId) {
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

    public static function search($arRequest) {
        $userId = $arRequest['userId'];
        if (empty($userId)) {
            throw new \Exception('Не указан идентификатор студента или преподавателя');
        }

        $role = Auth::getRole();
        if (!in_array($role, ['teacher', 'student'], true)) {
            throw new \Exception('Пользователь не входит в группу преподавателей или студентов');
        }

        $name = trim($arRequest['name'] ?? '');

        $idField = ($role === "teacher") ? 'UF_TEACHER_ID' : 'UF_STUDENT_ID';
        $hlBlock = ($role === "teacher") ? Constants::HLBLOCK_COURSES_TEACHERS_REL : Constants::HLBLOCK_COURSES_STUDENTS_REL;
        $rels = Entity::getInstance()->getList($hlBlock, [
            'filter' => [$idField => $userId]
        ]);

        $userCourseIds = self::mapRels($rels, 'UF_COURSE_ID');
        if (!$userCourseIds) {
            return [];
        }

        $params = [
            'ID' => $userCourseIds
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

    public static function delete($arRequest) {
        $courseId = $arRequest['courseId'];
        if (empty($courseId)) {
            throw new \Exception('Не указан идентификатор курса');
        }

        $deletingCourse = self::getBlockByCode($courseId);
        if (!$deletingCourse) {
            throw new \Exception('Курс не найден');
        }

        Entity::getInstance()->delete(Constants::HLBLOCK_COURSES, $courseId);
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