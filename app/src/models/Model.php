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
	 * @var array
	 */
	public static $has_many = [];

	/**
	 * @var array
	 */
	public static $has_one = [];

	/**
	 * Adiciona automaticamente a entity
	 * e o usuário que está criando o registro.
	 *
	 * @return void
	 */
	public function setUserAndEntity()
	{
		/**
		 * @var integer
		 */
		$this->entity = AuthSession::getEntity();

		/**
		 * @var integer
		 */
		$this->user_id = AuthSession::getUserId();
	}

	/**
	 * Pecorre os relacionamentos 1:N e 1:1
	 * e verifica se o registro está em us por algum deles.
	 *
	 * @throws \Exception <called class> está send usado por '<relation>'
	 * @return void
	 */
	public function inUsed()
	{
		/**
		 * Relacionamentos
		 * 
		 * @var array
		 */
		$relations = array_merge(static::$has_many, static::$has_one);

		foreach ($relations as $relation) {

			/**
			 * Nome do relacionamento um para muitos.
			 * 
			 * @var string
			 */
			$relation = $relation[0];

			if (count($this->{$relation})) {
				throw new \Exception(get_called_class() . " está sendo usado por '{$relation}'");
			}
		}
	}

	public static function find(/* $type, $options */)
	{
		$class = get_called_class();

		if (func_num_args() <= 0)
			throw new RecordNotFound("Couldn't find $class without an ID");

		$args = func_get_args();
		$options = static::extract_and_validate_options($args);
		$num_args = count($args);
		$single = true;

		if ($num_args > 0 && ($args[0] === 'all' || $args[0] === 'first' || $args[0] === 'last')) {
			switch ($args[0]) {
				case 'all':
					$single = false;
					break;

			 	case 'last':
					if (!array_key_exists('order',$options)) {
						$options['order'] = join(' DESC, ',static::table()->pk) . ' DESC';
					} else {
						$options['order'] = SQLBuilder::reverse_order($options['order']);
					}
			 	case 'first':
			 		$options['limit'] = 1;
			 		$options['offset'] = 0;
			 		break;
			}

			$args = array_slice($args,1);
			$num_args--;
		} elseif (1 === count($args) && 1 == $num_args){ //find by pk
			$args = $args[0];
		}

		if (AuthSession::isAuthenticated()) {
			$entity = AuthSession::getEntity();

			if (! isset($options['conditions'])) {
				$options['conditions'] = ['entity = ?', $entity];
			} else {
				$options['conditions'][0] .= ' and entity = ?';
				$options['conditions'][] = $entity;
			}
		}

		// anything left in $args is a find by pk
		if ($num_args > 0 && !isset($options['conditions'])) {
			return static::find_by_pk($args, $options);
		}

		$options['mapped_names'] = static::$alias_attribute;
		$list = static::table()->find($options);

		return $single ? (!empty($list) ? $list[0] : null) : $list;
	}
}