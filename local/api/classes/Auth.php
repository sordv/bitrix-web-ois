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

    public static function getRole($id) {
        global $USER;

        $groups = $USER::GetUserGroup($id);

        $rs = \CGroup::GetList($by="c_sort", $order="asc", [
            "ID" => implode("|", $groups)
        ]);


        $names = [];
        while ($group = $rs->Fetch()) {
            $names[] = $group["NAME"];
        }

        if (in_array("Преподаватели", $names)) {
            return "teacher";
        } else if (in_array("Студенты", $names)) {
            return "student";
        }
        return "";
    }

    public static function logout() {
        global $USER;

        $USER->Logout();
    }
}