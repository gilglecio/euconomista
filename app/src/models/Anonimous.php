<?php

/**
 * Anonimous model
 */

use App\Mail\Mailer;
use App\Util\Toolkit;

/**
 * Esta classe faz referencia a tabela `users` no banco de dados.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class Anonimous extends User
{
    /**
     * Nome da tabela.
     *
     * @var string
     */
    public static $table_name = 'users';

    /**
     * Este método foi reescrito para que não seja setado a coluna `entity` e `user_id` automaticamente.
     */
    public function setUserAndEntity()
    {
        $this->confirm_email_token = ENV_TEST ? sha1($this->email) : Toolkit::uniqHash($this->id);
        $this->confirm_email_token_date = (new \Datetime())->add(new \Dateinterval('P1D'));
    }

    /**
     * Faz o cadastro de um usuário de fora do sistema.
     *
     * @param array $fields
     * @throws \Exception As senhas não conferem.
     * @throws \Exception Usuário já cadastrado no sistema.
     * @throws \Exception Nenhum registro localizado.
     * @return User
     */
    public static function register($fields)
    {
        if ($fields['password'] != $fields['confirm_password']) {
            throw new \Exception('As senhas não conferem.');
        }

        if (self::count(['conditions' => ['email = ?', $fields['email']]])) {
            throw new \Exception('Usuário já cadastrado no sistema.');
        }

        $entity = self::find_by_sql('select max(entity) as entity from users limit 1');

        $fields['entity'] = ((int) $entity[0]->entity) + 1;

        return self::generate($fields);
    }

    public static function confirmEmail($token)
    {
        $conditions = [
            'conditions' => [
                'confirm_email_token = ?', 
                $token
            ]
        ];

        if (! $user = self::find('first', $conditions)) {
            throw new \Exception('Token não localizado.');
        }

        $date = $user->confirm_email_token_date;

        $user->resetConfirmToken();

        if ((new \Datetime) > $date) {
            throw new \Exception('Token vencido.');
        }

        $user->status = self::STATUS_CONFIRMED;
        $user->save();

        if ($user->is_invalid()) {
            throw new \Exception($user->getFisrtError());
        }

        return $user;
    }

    public function resetConfirmToken()
    {
        $this->confirm_email_token = null;
        $this->confirm_email_token_date = null;
        $this->save();

        if ($this->is_invalid()) {
            throw new \Exception($this->getFisrtError());
        }
    }
}
