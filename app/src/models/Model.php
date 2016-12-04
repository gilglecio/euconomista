<?php

use App\Auth\AuthSession;

abstract class Model extends ActiveRecord\Model
{
	/**
	 * @var array
	 */
	public static $before_create = [
		'setUserAndEntity'
	];

	/**
	 * Adiciona automaticamente a entity
	 * e o usuário que está criando o registro.
	 *
	 * @return void
	 */
	public function setUserAndEntity()
	{
		$this->entity = AuthSession::getEntity();
		$this->user_id = AuthSession::getUserId();
	}
}