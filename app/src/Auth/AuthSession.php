<?php

/**
 * Authentication
 *
 * @package App\Auth
 * @version v1.0
 *
 * @uses App\Interfaces\UserAuthInterface
 */
namespace App\Auth;

use App\Interfaces\UserAuthInterface;

/**
 * User authentication
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
class AuthSession
{
    /**
     * User session name
     */
    const AUTH_SESSION_NAME = 'user';

    /**
     * Faz a tentiva de login com o $email e $password.
     *
     * @param UserAuthInterface $user
     * @param string            $email
     * @param string            $password
     *
     * @throws \Exception Este e-mail não está cadastrado.
     * @throws \Exception A senha fornecida não confere com o cadastro do usuário.
     *
     * @return bool
     */
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

    static function createSession(UserAuthInterface $user)
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

    /**
     * Verifica se existe um usuário logado.
     *
     * @return boolean
     */
    public static function isAuthenticated()
    {
        return isset($_SESSION[self::AUTH_SESSION_NAME]);
    }

    /**
     * Retorna o ID do usuário logado.
     *
     * @return integer
     */
    public static function getUserId()
    {
        if (! self::isAuthenticated()) {
            return;
        }

        return $_SESSION[self::AUTH_SESSION_NAME]['id'];
    }

    /**
     * Retorna o numero da entidade do usuário logado.
     *
     * @return integer
     */
    public static function getEntity()
    {
        return $_SESSION[self::AUTH_SESSION_NAME]['entity'];
    }

    /**
     * Limpa a sessão que armazena os dados do usuário logado.
     *
     * @return boolean
     */
    public static function clear()
    {
        unset($_SESSION[self::AUTH_SESSION_NAME]);
        
        return true;
    }
}
