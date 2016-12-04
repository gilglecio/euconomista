<?php

use App\Auth\AuthSession;

class Model extends ActiveRecord\Model
{
	/**
	 * Callback before create model
	 *  
	 * @var array
	 */
	public static $before_create = [
		'setUserAndEntity'
	];

	/**
	 * Setter user_id and entity
	 *
	 * @return void
	 */
	public function setUserAndEntity()
	{
		$this->entity = AuthSession::getEntity();
		$this->user_id = AuthSession::getUserId();
	}
}