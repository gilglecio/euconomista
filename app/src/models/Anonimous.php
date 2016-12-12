<?php

/**
 * Anonimous model
 */

/**
 * Esta classe faz referencia a tabela `users` no banco de dados.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class Anonimous extends User
{
    /**
     * Nome da tabela.
     * @var string
     */
    public static $table_name = 'users';

    /**
     * Este método foi reescrito para que não seja setado a coluna `entity` e `user_id` automaticamente.
     */
    public function setUserAndEntity()
    {
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
}
