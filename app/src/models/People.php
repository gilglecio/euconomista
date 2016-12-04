<?php

final class People extends Model
{
	/**
	 * @var array
	 */
	static $validates_presence_of = [
		['name']
	];

	/**
	 * @var array
	 */
	static $validates_uniqueness_of = [
		['name', 'entity']
	];

	/**
	 * @var array
	 */
	static $validates_length_of = [
		['name', 'within' => [3, 60]]
	];

	/**
	 * Salva um pessoa no banco de dados.
	 * 
	 * @param array $fields
	 * @throws \Exception Mensagem de erro do model
	 * @return People
	 */
	static function generate($fields)
	{
		/**
		 * @var People
		 */
		$row = self::create([
			'name' => $fields['name']
		]);

		if ($row->is_invalid()) {
			throw new \Exception($row->errors->full_messages()[0]);
		}

		return $row;
	}
}