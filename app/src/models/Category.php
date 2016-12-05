<?php

final class Category extends Model
{
	/**
	 * @var array
	 */
	public static $validates_presence_of = [
		['name']
	];

	/**
	 * @var array
	 */
	public static $validates_uniqueness_of = [
		['name', 'entity']
	];

	/**
	 * @var array
	 */
	public static $validates_length_of = [
		['name', 'within' => [3, 25]]
	];

	/**
	 * @var array
	 */
	public static $has_many = [
		['releases']
	];

	/**
	 * Salva uma categoria no banco de dados.
	 * 
	 * @param array $fields
	 * @throws \Exception Mensagem de erro do model
	 * @return People
	 */
	public static function generate($fields)
	{
		/**
		 * @var Category
		 */
		$row = self::create([
			'name' => $fields['name']
		]);

		if ($row->is_invalid()) {
			throw new \Exception($row->errors->full_messages()[0]);
		}

		return $row;
	}

	/**
	 * Apaga uma categoria pelo ID.
	 * 
	 * @param integer $category_id
	 * @throws \Exception A categoria éstá sendo usada por lançamentos.
	 * @throws \Exception Categoria #{$category_id} não foi apagada.
	 * @throws \Exception Categoria não localizada.
	 * @return boolean
	 */
	public static function remove($category_id)
	{
		/**
		 * @var Category
		 */
		if (! $category = self::find($category_id)) {
			throw new \Exception('Categoria não localizada.');
		}

		try {
			$category->inUsed();
		} catch (\Exception $e) {
			throw $e;
		}

		if (! $category->delete()) {
			throw new \Exception("Categoria #{$category_id} não foi apagada.");
		}

		return true;
	}
}