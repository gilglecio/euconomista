<?php

/**
 * Model User.
 */

use App\Interfaces\UserAuthInterface;
use App\Auth\AuthSession;

/**
 * Esta classe faz referencia a tabela `users` no banco de dados.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
class User extends Model implements UserAuthInterface
{
    const STATUS_UNCONFIRMED = 0;
    const STATUS_CONFIRMED = 1;

    /**
     * Validação de campos obrigatŕios.
     *
     * @var array
     */
    public static $validates_presence_of = [
        ['name'],
        ['email'],
        ['password'],
    ];

    /**
     * Validação para saber se o email passado é válido.
     *
     * @var array
     */
    public static $validates_format_of = [
        ['email', 'with' => '/^.*?@.*$/']
    ];

    /**
     * Validação para não permitir que exista dois usuários com dois emails iguais.
     *
     * @var array
     */
    public static $validates_uniqueness_of = [
        ['email']
    ];

    /**
     * Validação para definir a quantidade de caracteres campo a campo.
     *
     * @var array
     */
    public static $validates_length_of = [
        ['name', 'within' => [3, 45]],
        ['email', 'within' => [5, 60]]
    ];

    /**
     * Define os relacionamentos 1:N.
     *
     * @var array
     */
    public static $has_many = [
        ['categories'],
        ['peoples'],
        ['users'],
        ['releases']
    ];

    /**
     * Callbacks que devem ser executados toda vez que um usuário é criado.
     *
     * - O callback `setUserAndEntity` serve para setar
     * automaticamente a coluna `entity` e a coluna `user_id`.
     * - O callback `encryptPassword` é invocado para encriptar a senha do usuário.
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
            'email' => $fields['email'],
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

    /**
     * Personaliza a descrição dos logs, ao criar, editar e apagar.
     *
     * @param string $action A ação pode ser `create`, `update` ou `destroy`.
     * @return string Frase personalizada confirme ação.
     */
    public function getLogDescription($action)
    {
        return [
            'create' => "Adicionou '{$this->name} <{$this->email}>' como usuário do sistema.",
            'update' => "Editou o cadastro de '{$this->backup_for_log->name} <{$this->backup_for_log->email}>'.",
            'destroy' => "Apagou '{$this->name} <{$this->email}>' dos usuários.",
        ][$action];
    }
}
