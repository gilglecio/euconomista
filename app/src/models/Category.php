<?php

class Category extends Model
{
	public static $validates_presence_of = [
		['name']
	];

	public static $validates_uniqueness_of = [
		['name', 'entity']
	];

	public static $validates_length_of = [
		['name', 'within' => [3, 25]]
	];

	/**
	 * Salva um pessoa no banco de dados.
	 * 
	 * @param array $fields
	 * @return People
	 */
	public static function generate($fields)
	{
		/**
		 * @var Category
		 */
		$row = Category::create([
			'name' => $fields['name']
		]);

		if ($row->is_invalid()) {
			throw new \Exception($row->errors->full_messages()[0]);
		}

		return $row;
	}
}