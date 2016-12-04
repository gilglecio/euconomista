<?php

use App\Interfaces\UserAuthInterface;

final class User extends Model implements UserAuthInterface
{
	/**
	 * @var array
	 */
	static $validates_presence_of = [
		['name'],
		['email'],
		['password'],
	];

	/**
	 * @var array
	 */
	static $validates_format_of = [
		['email', 'with' => '/^.*?@.*$/']
	];

	/**
	 * @var array
	 */
	static $validates_uniqueness_of = [
		['email']
	];

	/**
	 * @var array
	 */
	static $validates_length_of = [
		['name', 'within' => [3, 45]],
		['email', 'within' => [5, 60]]
	];

	/**
	 * @var array
	 */
	static $before_create = [
		'setUserAndEntity',
		'encryptPassword'
	];

	/**
	 * Encripta a senha do usuário.
	 * 
	 * @return void
	 */
	public function encryptPassword()
	{
		$this->assign_attribute('password', password_hash($this->password, PASSWORD_DEFAULT));
	}

	/**
	 * Retorna o id, entity, password e name do usuário.
	 * Método utilizado na authenticação.
	 * 
	 * @param string $email
	 * @return \stdClass
	 */
	public function getIdEntityPasswordAndNameByEmail($email)
	{
		if (! $user = self::find('first', ['conditions' => ['email = ?', $email]])) {
			return null;
		}

		$user = (object) $user->to_array();

		return (object) [
			'id' => $user->id,
			'email' => $user->email,
			'entity' => $user->entity,
			'name' => $user->name,
			'password' => $user->password
		];
	}

	/**
	 * Salva um usuário no banco de dados.
	 * 
	 * @param array $fields
	 * @throws \Exception Mensagem de erro do model.
	 * @return User
	 */
	static function generate($fields)
	{
		/**
		 * @var User
		 */
		$row = self::create([
			'name' => $fields['name'],
			'email' => $fields['email'],
			'password' => $fields['password']
		]);

		if ($row->is_invalid()) {
			throw new \Exception($row->errors->full_messages()[0]);
		}

		return $row;
	}
}