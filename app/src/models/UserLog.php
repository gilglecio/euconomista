<?php

/**
 * UserLog model
 * @uses App\Auth\AuthSession
 */

use App\Auth\AuthSession;

/**
 * Esta classe faz referencia a tabela `user_logs` no banco de dados.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
class UserLog extends Model
{
    /**
     * Define os relacionamentos 1:1.
     *
     * @var array
     */
    public static $belongs_to = [
        ['user']
    ];

    /**
     * Cadastra um log do tipo `login`.
     *
     * @return UserLog
     */
    public static function logout()
    {
        if (! User::find(AuthSession::getUserId())) {
            return null;
        }

        $log = self::create([
            'action' => 'logout',
            'class_name' => AuthSession::class,
            'row_id' => AuthSession::getUserId(),
            'description' => 'Desconectou-se'
        ]);

        if ($log->is_invalid()) {
            throw new \Exception($log->getFisrtError());
        }

        return $log;
    }

    /**
     * Cadastra um log do tipo `logout`.
     *
     * @return UserLog
     */
    public static function login()
    {
        $log = self::create([
            'action' => 'login',
            'class_name' => AuthSession::class,
            'row_id' => AuthSession::getUserId(),
            'description' => 'Conectou-se'
        ]);

        if ($log->is_invalid()) {
            throw new \Exception($log->getFisrtError());
        }

        return $log;
    }

    /**
     * Registra um log no banco de dados.
     *
     * @param array $data Informações do log.
     * @return UserLog
     */
    public static function register($data)
    {
        /**
         * Descrição personalizada para cada action.
         * @var string
         */
        $description = $data['model']->getLogDescription($data['action']);

        if (is_null($description)) {
            return;
        }

        if (is_null($data['model']->user_id)) {
            return;
        }

        /**
         * Nome do model
         * @var string
         */
        $class_name = get_class($data['model']);

        /**
         * Evita looping infinito.
         */
        if ($class_name == self::class) {
            return;
        }

        /**
         * @var UserLog
         */
        $log = self::create([
            'action' => $data['action'],
            'class_name' => $class_name,
            'row_id' => $data['model']->id,
            'description' => $description,
        ]);

        if ($log->is_invalid()) {
            throw new \Exception($log->getFisrtError());
        }

        return $log;
    }

    /**
     * Verifica se o log é o último, para isso é verificado se existe algum log que a coluna `class_name` e `row_id` possua o id maior que o log atual.
     *
     * @return boolean Retorna TRUE se o log for o último.
     */
    public function isLastLog()
    {
        $find = self::find('last', [
            'conditions' => [
                '`row_id` = ? and `class_name` = ?',
                $this->row_id,
                $this->class_name
            ]
        ]);

        return $find->id == $this->id;
    }
}
