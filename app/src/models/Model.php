<?php

use App\Auth\AuthSession;

class Model extends ActiveRecord\Model
{
	/**
	 * @param array $fields
	 * @return ActiveRecord\Model
	 */
	static function getModelToSave($fields)
	{
		$id = isset($fields['id']) && is_numeric($fields['id']);

		$model = get_called_class();

		if ($id) {
			return static::find($id);
		}

		$row = new $model;
		$row->entity = AuthSession::getEntity();
		$row->user_id = AuthSession::getUserId();

		return $row;
	}	
}