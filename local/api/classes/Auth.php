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
}