<?php

use App\Auth\AuthSession;

class UserLog extends Model
{
    public static $belongs_to = [
        ['user']
    ];

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

    public static function register($data)
    {
        $description = $data['model']->getLogDescription($data['action']);

        if (is_null($description)) {
            return;
        }

        if (is_null($data['model']->user_id)) {
            return;
        }

        $class_name = get_class($data['model']);

        /**
         * Evita looping infinito.
         */
        if ($class_name == self::class) {
            return;
        }

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
     * Verifica se o log é o último, para isso é verificado se existe algum log 
     * que a coluna `class_name` e `row_id` possua o id maior que o log atual.
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
