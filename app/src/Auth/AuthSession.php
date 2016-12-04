<?php

/**
 * @package AuthSession
 * @subpackage App\Auth
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 * 
 * @uses App\Interfaces\UserAuthInterface
 */
namespace App\Auth;

use App\Interfaces\UserAuthInterface;

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
	static function attemp(UserAuthInterface $user, $email, $password)
	{
		if (! $user = $user->getIdEntityPasswordAndNameByEmail($email)) {
			throw new \Exception('Este e-mail não está cadastrado.');
		}

		if (! password_verify($password, $user->password)) {
			throw new \Exception('A senha fornecida não confere com o cadastro do usuário.');
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

	/**
	 * Limpa a sessão que armazena os dados do usuário logado.
	 * 
	 * @return boolean
	 */
	static function clear()
	{
		unset($_SESSION[self::AUTH_SESSION_NAME]);
		
		return true;
	}
}