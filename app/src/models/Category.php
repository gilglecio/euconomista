<?php

/**
 * Category model.
 */

/**
 * Esta classe faz referencia a tabela `categories` no banco de dados.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class Category extends Model
{
	/**
	 * Validação dos campos obrigatórios.
	 * @var array
	 */
	public static $validates_presence_of = [
		['name']
	];

	/**
	 * Validação que não permite que haja na mesma entidade duas categorias com o mesmo nome.
	 * @var array
	 */
	public static $validates_uniqueness_of = [
		['name', 'entity']
	];

	/**
	 * Validação para que define o limite mínimo e máximo de caracteres que a coluna `name` pode ter.
	 * @var array
	 */
	public static $validates_length_of = [
		['name', 'within' => [3, 25]]
	];

	/**
	 * Define os relacionamentos 1:N.
	 * @var array
	 */
	public static $has_many = [
		['releases']
	];

	/**
	 * Salva uma categoria no banco de dados.
	 * 
	 * @param array $fields
	 * @throws \Exception Mensagem de erro do model.
	 * @throws \Exception Categoria não localizada.
	 * @return People
	 */
	public static function generate($fields)
	{
		if (isset($fields['id']) && is_numeric($fields['id'])) {
			
			/**
			 * @var Category
			 */
			if (! $row = self::find($fields['id'])) {
				throw new \Exception('Categoria não localizada.');
			}

			$row->name = $fields['name'];
			$row->save();

		} else {

			/**
			 * @var Category
			 */
			$row = self::create([
				'name' => $fields['name']
			]);
		}

		if ($row->is_invalid()) {
			throw new \Exception($row->getFisrtError());
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

	/**
	 * Personaliza a descrição dos logs, ao criar, editar e apagar.
	 * 
	 * @param string $action A ação pode ser `create`, `update` ou `destroy`.
	 * @return string Frase personalizada confirme ação.
	 */
	public function getLogDescription($action)
	{
		return [
			'create' => "Criou a categotia '{$this->name}'.",
			'update' => "Alterou o nome da categoria '{$this->backup_for_log->name}' para '{$this->name}'.",
			'destroy' => "Apagou a categoria '{$this->name}'.",
		][$action];
	}
}