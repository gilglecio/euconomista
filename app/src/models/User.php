<?php

use App\Interfaces\UserAuthInterface;
use App\Auth\AuthSession;

class User extends Model implements UserAuthInterface
{
	/**
	 * @var string
	 */
	static $table_name = 'users';

	/**
	 * @var array
	 */
	public static $validates_presence_of = [
		['name'],
		['email'],
		['password'],
	];

	/**
	 * @var array
	 */
	public static $validates_format_of = [
		['email', 'with' => '/^.*?@.*$/']
	];

	/**
	 * @var array
	 */
	public static $validates_uniqueness_of = [
		['email']
	];

	/**
	 * @var array
	 */
	public static $validates_length_of = [
		['name', 'within' => [3, 45]],
		['email', 'within' => [5, 60]]
	];

	/**
	 * @var array
	 */
	public static $has_many = [
		['categories'],
		['peoples'],
		['users'],
		['releases']
	];

	/**
	 * @var array
	 */
	public static $before_create = [
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
	public static function generate($fields)
	{
		/**
		 * @var User
		 */
		$row = self::create([
			'entity' => isset($fields['entity']) ? $fields['entity'] : null,
			'name' => $fields['name'],
			'email' => $fields['email'],
			'password' => $fields['password']
		]);

		if ($row->is_invalid()) {
			throw new \Exception($row->errors->full_messages()[0]);
		}

		return $row;
	}

	/**
	 * Apaga uma usuário pelo ID.
	 * Verifica o usuário pode ser apagado.
	 * 
	 * @param integer $user_id
	 * @throws \Exception User está sendo usada por '{$relation}'.
	 * @throws \Exception Você não pode apagar a si mesmo.
	 * @throws \Exception Usuário <user_id> não foi apagada.
	 * @return boolean
	 */
	public static function remove($user_id)
	{
		/**
		 * @var User
		 */
		if (! $user = self::find($user_id)) {
			throw new \Exception('Usuário não localizado.');
		}

		try {
			$user->inUsed();
		} catch (\Exception $e) {
			throw $e;
		}

		if ($user->id == AuthSession::getUserId()) {
			throw new \Exception('Você não pode apagar a si mesmo.');
		}

		if (! $user->delete()) {
			throw new \Exception("Usuário #{$user_id} não foi apagada.");
		}

		return true;
	}

	public function getLogDescription($action)
	{
		return [
			'create' => "Adicionou '{$this->name} <{$this->email}>' como usuário do sistema.",
			'update' => "Editou o cadastro de '{$this->backup_for_log->name} <{$this->backup_for_log->email}>'.",
			'destroy' => "Apagou '{$this->name} <{$this->email}>' dos usuários.",
		][$action];
	}
}