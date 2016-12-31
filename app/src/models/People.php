<?php

/**
 * Model People.
 */

/**
 * Esta classe faz referencia a tabela `peoples` no banco de dados.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class People extends Model
{
    /**
     * Validação de campos obrigatórios.
     *
     * @var array
     */
    public static $validates_presence_of = [
        ['name', 'message' => 'Favor informar o nome da pessoa']
    ];

    /**
     * Não permite que na entidade exista duas pessoas com o mesmo nome.
     *
     * @var array
     */
    public static $validates_uniqueness_of = [
        ['name', 'entity', 'message' => 'Já existe uma pessoa com este nome']
    ];

    /**
     * Validação para limitir a quantidade de caracteres da coluna `name` entre 3 e 60.
     *
     * @var array
     */
    public static $validates_length_of = [
        ['name', 'within' => [3, 60], 'message' => 'O nome da categoria deve está entre 03 e 60 caracteres']
    ];

    /**
     * Define os relacionamentos 1:N.
     *
     * @var array
     */
    public static $has_many = [
        ['releases']
    ];

    /**
     * Salva um pessoa no banco de dados.
     *
     * @param array $fields
     * @throws \Exception Mensagem de erro do model.
     * @throws \Exception Pessoa não localizada.
     * @return People
     */
    public static function generate($fields)
    {
        if (isset($fields['id']) && is_numeric($fields['id'])) {
            
            /**
             * @var People
             */
            if (! $row = self::find($fields['id'])) {
                throw new \Exception('Pessoa não localizada.');
            }

            $row->name = $fields['name'];
            $row->save();
        } else {

            /**
             * @var People
             */
            $row = self::create([
                'name' => $fields['name']
            ]);
        }

        if ($row->is_invalid()) {
            throw new \Exception($row->getFisrtError());
        }

        return $row;
    }

    /**
     * Cria uma pessoa se a mesma não existir.
     *
     * @author Gilglécio Santos de Oliveira <gilglecio_765@hotmail.com>
     * @author Fernando Dutra Neres <fernando@inova2b.com.br>
     * @param  string $name Nome da pessoa
     * @return Category
     */
    public static function saveIfNotExists($name)
    {
        if (! $find = self::find_by_name($name)) {
            $create = self::create(['name' => $name]);

            if ($create->is_invalid()) {
                throw new \Exception($create->getFisrtError());
            }

            return $create;
        }

        return $find;
    }

    /**
     * Apaga uma pessoa pelo ID.
     *
     * @param integer $people_id
     * @throws \Exception A pessoa éstá sendo usada por lançamentos.
     * @throws \Exception Pessoa #{$people_id} não foi apagada.
     * @throws \Exception Pessoa não localizada.
     * @return boolean
     */
    public static function remove($people_id)
    {
        /**
         * @var People
         */
        if (! $people = self::find($people_id)) {
            throw new \Exception('Pessoa não localizada.');
        }

        try {
            $people->inUsed();
        } catch (\Exception $e) {
            throw $e;
        }

        if (! $people->delete()) {
            throw new \Exception("Pessoa #{$people_id} não foi apagada.");
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
            'create' => "Adicionou '{$this->name}' em pessoas.",
            'update' => "Alterou o nome de '{$this->backup_for_log->name}' para '{$this->name}'.",
            'destroy' => "Apagou '{$this->name}' de pessoas.",
        ][$action];
    }
}
