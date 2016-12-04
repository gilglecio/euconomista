<?php

use App\Interfaces\UserAuthInterface;

class User extends Model implements UserAuthInterface
{
	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var string
	 */
	protected $password;

	static $validates_presence_of = [
		['name'],
		['email'],
		['password'],
	];

	static $validates_format_of = [
		['email', 'with' => '/^.*?@.*$/']
	];

	static $validates_uniqueness_of = [
		['email']
	];

	static $validates_length_of = [
		['name', 'within' => [3, 45]],
		['email', 'within' => [5, 60]],
		['password', 'within' => [6, 15]],
	];

	/**
	 * @param string $email
	 * @return \stdClass|null
	 */
	public function getIdPasswordAndNameByEmail($email)
	{
		if (! $user = self::find('first', ['conditions' => ['email = ?', $email]])) {
			return null;
		}

		$user = (object) $user->to_array();

		return (object) [
			'id' => $user->id,
			'email' => $user->email,
			'name' => $user->name,
			'password' => $user->password
		];
	}
}