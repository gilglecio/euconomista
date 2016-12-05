<?php

final class Anonimous extends User
{
	public function setUserAndEntity()
	{
	}

	/**
	 * Faz o cadastro de um usuário de fora do sistema.
	 * 
	 * @param array $fields
	 * @throws \Exception As senhas não conferem.
	 * @throws \Exception Usuário já cadastrado no sistema.
	 * @throws \Exception Nenhum registro localizado.
	 * @return User
	 */
	public static function register($fields)
	{
		if ($fields['password'] != $fields['confirm_password']) {
			throw new \Exception('As senhas não conferem.');
		}

		if (self::count(['conditions' => ['email = ?', $fields['email']]])) {
			throw new \Exception('Usuário já cadastrado no sistema.');
		}

		if (! $entity = self::find('last', ['order' => 'entity desc'])) {
			throw new \Exception('Nenhum registro localizado.');
		}

		$fields['entity'] = $entity->entity + 1;

		return self::generate($fields);
	}
}