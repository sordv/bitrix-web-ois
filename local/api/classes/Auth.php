<?php

namespace Legacy\API;

class Auth
{
    public static function login($arRequest) {
        $login = $arRequest['login'];
        $password = $arRequest['password'];

        global $USER;

        $result = $USER->Login($login, $password, 'Y');

        if (is_array($result) && $result['TYPE'] === 'ERROR') {
            return [
                'success' => false,
                'error' => $result['MESSAGE']
            ];
        } else {
            return [
                'success' => true,
                'userId' => $USER->GetID()
            ];
        }
    }

    public static function logout() {
        global $USER;

        $USER->Logout();
    }

    public static function getRole() {
        global $USER;

        $userId = $USER->GetID();
        $groups = $USER::GetUserGroup($userId);

        $groupList = \CGroup::GetList($by="c_sort", $order="asc", [
            "ID" => implode("|", $groups)
        ]);

        $names = [];
        while ($group = $groupList->Fetch()) {
            $names[] = $group["NAME"];
        }

        if (in_array("Преподаватели", $names)) {
            return "teacher";
        } else if (in_array("Студенты", $names)) {
            return "student";
        }
        return "";
    }
}