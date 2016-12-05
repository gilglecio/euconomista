<?php

final class People extends Model
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
		['name', 'within' => [3, 60]]
	];

	/**
	 * @var array
	 */
	public static $has_many = [
		['releases']
	];

	/**
	 * Salva um pessoa no banco de dados.
	 * 
	 * @param array $fields
	 * @throws \Exception Mensagem de erro do model
	 * @return People
	 */
	public static function generate($fields)
	{
		if (isset($fields['id']) && is_numeric($fields['id'])) {
			
			/**
			 * @var People
			 */
			$row = People::find($fields['id']);
			$row->name = $fields['name'];
			$row->save();

		} else {

			/**
			 * @var People
			 */
			$row = self::create([
				'name' => $fields['name']
			]);
		}

		if ($row->is_invalid()) {
			throw new \Exception($row->errors->full_messages()[0]);
		}

		return $row;
	}

	/**
	 * Apaga uma pessoa pelo ID.
	 * 
	 * @param integer $people_id
	 * @throws \Exception A pessoa éstá sendo usada por lançamentos.
	 * @throws \Exception Pessoa #{$people_id} não foi apagada.
	 * @throws \Exception Pessoa não localizada.
	 * @return boolean
	 */
	public static function remove($people_id)
	{
		/**
		 * @var People
		 */
		if (! $people = self::find($people_id)) {
			throw new \Exception('Pessoa não localizada.');
		}

		try {
			$people->inUsed();
		} catch (\Exception $e) {
			throw $e;
		}

		if (! $people->delete()) {
			throw new \Exception("Pessoa #{$people_id} não foi apagada.");
		}

		return true;
	}
}