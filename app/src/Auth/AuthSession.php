<?php

namespace App\Auth;

use App\Interfaces\UserAuthInterface;

class AuthSession
{
	/**
	 * User session name
	 */
	const AUTH_SESSION_NAME = 'user';

	/**
	 * @param UserAuthInterface $user
	 * @param string            $email
	 * @param string            $password
	 * 
	 * @throws \Exception User email not found
	 * @throws \Exception User password not match
	 * 
	 * @return bool
	 */
	static function attemp(UserAuthInterface $user, $email, $password)
	{
		if (! $user = $user->getIdEntityPasswordAndNameByEmail($email)) {
			throw new \Exception('User email not found.');
		}

		if (! password_verify($password, $user->password)) {
			throw new \Exception('User password not match.');
		}

		$_SESSION[self::AUTH_SESSION_NAME] = [
			'id' => $user->id,
			'name' => $user->name,
			'entity' => $user->entity,
			'email' => $user->email
		];

		return true;
	}

	/**
	 * Retorna o ID do usuário logado.
	 * 
	 * @return integer
	 */
	static function getUserId()
	{
		return $_SESSION[self::AUTH_SESSION_NAME]['id'];
	}

	/**
	 * Retorna o numero da entidade do usuário logado.
	 * 
	 * @return integer
	 */
	static function getEntity()
	{
		return $_SESSION[self::AUTH_SESSION_NAME]['entity'];
	}

	static function clear()
	{
		unset($_SESSION[self::AUTH_SESSION_NAME]);
		
		return true;
	}
}