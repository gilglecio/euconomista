<?php

use App\Interfaces\UserAuthInterface;
use App\Auth\AuthSession;

class User extends Model implements UserAuthInterface
{
    const STATUS_UNCONFIRMED = 0;
    const STATUS_CONFIRMED = 1;

    public static $validates_presence_of = [
        ['name'],
        ['email'],
        ['password'],
    ];

    public static $validates_format_of = [
        ['email', 'with' => '/^.*?@.*$/']
    ];

    public static $validates_uniqueness_of = [
        ['email']
    ];

    public static $validates_length_of = [
        ['name', 'within' => [3, 45]],
        ['email', 'within' => [5, 60]]
    ];

    public static $has_many = [
        ['categories'],
        ['peoples'],
        ['users'],
        ['releases']
    ];

    public static $before_create = [
        'setUserAndEntity',
        'encryptPassword'
    ];

    public function encryptPassword()
    {
        $this->assign_attribute('password', password_hash($this->password, PASSWORD_DEFAULT));
    }

    /**
     * Retorna o id, entity, password e name do usuário.
     * Método utilizado na authenticação.
     */
    public function getIdEntityPasswordStatusAndNameByEmail($email)
    {
        if (! $user = self::find('first', ['conditions' => ['email = ?', $email]])) {
            return null;
        }

        if ($user->status == self::STATUS_UNCONFIRMED) {
            throw new \Exception('E-mail não confirmado.');
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

    public static function generate($fields)
    {
        $row = self::create([
            'entity' => isset($fields['entity']) ? $fields['entity'] : null,
            'name' => $fields['name'],
            'email' => $fields['email'],
            'status' => 1, // USUÁRIO CONFIRMADO
            'password' => $fields['password']
        ]);

        if ($row->is_invalid()) {
            throw new \Exception($row->getFisrtError());
        }

        return $row;
    }

    public function getFirstName()
    {
        return explode(' ', $this->name)[0];
    }

    public static function remove($user_id)
    {
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
