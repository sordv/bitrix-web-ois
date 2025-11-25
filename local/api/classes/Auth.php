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

    public static function getUser()
    {
        global $USER;

        if (!$USER->IsAuthorized()) {
            throw new \Exception('Пользователь не авторизован');
        }

        $rsUser = \CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();

        return self::mapUser($arUser);
    }

    private static function mapUser($user) {
        return [
            'code'        => $user['ID'],
            'name'        => $user['NAME'],
            'lastName'    => $user['LAST_NAME'],
            'patronymic'  => $user['SECOND_NAME'],
            'email'       => $user['EMAIL'],
        ];
    }
}
