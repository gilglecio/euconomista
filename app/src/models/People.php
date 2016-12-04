<?php

class People extends Model
{
	static $validates_presence_of = [
		['name']
	];

	static $validates_uniqueness_of = [
		['name', 'entity']
	];

	static $validates_length_of = [
		['name', 'within' => [3, 60]]
	];

	/**
	 * Salva um pessoa no banco de dados.
	 * 
	 * @param array $fields
	 * @return People
	 */
	static function generate($fields)
	{
		/**
		 * @var People
		 */
		$row = People::create([
			'name' => $fields['name']
		]);

		if ($row->is_invalid()) {
			throw new \Exception($row->errors->full_messages()[0]);
		}

		return $row;
	}
}