<?php

namespace App\Auth;

use App\Interfaces\UserAuthInterface;

class AuthSession
{
    const AUTH_SESSION_NAME = 'user';

    public static function attemp(UserAuthInterface $user, $email, $password)
    {
        if (! $user = $user->getIdEntityPasswordStatusAndNameByEmail($email)) {
            throw new \Exception('Este e-mail não está cadastrado.');
        }

        if (! password_verify($password, $user->password)) {
            throw new \Exception('A senha fornecida não confere com o cadastro do usuário.');
        }

        self::createSession($user);

        return true;
    }

    static function createSession($user)
    {
        $_SESSION[self::AUTH_SESSION_NAME] = [
            'id' => $user->id,
            'name' => $user->name,
            'entity' => $user->entity,
            'email' => $user->email
        ];
    }

    public static function attempFb(UserAuthInterface $user, $email)
    {
        if (! $user = $user->getIdEntityPasswordStatusAndNameByEmail($email)) {
            return false;
        }

        self::createSession($user);

        return true;
    }

    public static function isAuthenticated()
    {
        return isset($_SESSION[self::AUTH_SESSION_NAME]);
    }

    public static function getUserId()
    {
        if (! self::isAuthenticated()) {
            return;
        }

        return $_SESSION[self::AUTH_SESSION_NAME]['id'];
    }

    public static function getEntity()
    {
        return $_SESSION[self::AUTH_SESSION_NAME]['entity'];
    }

    public static function clear()
    {
        unset($_SESSION[self::AUTH_SESSION_NAME]);
        
        return true;
    }
}
