<?php

final class People extends Model
{
    public static $validates_presence_of = [
        ['name', 'message' => 'Favor informar o nome da pessoa']
    ];

    public static $validates_uniqueness_of = [
        [['name', 'entity'], 'message' => 'Já existe uma pessoa com este nome']
    ];

    public static $validates_length_of = [
        ['name', 'within' => [3, 60], 'message' => 'O nome da categoria deve está entre 03 e 60 caracteres']
    ];

    public static $has_many = [
        ['releases']
    ];

    public static function generate($fields)
    {
        if (isset($fields['id']) && is_numeric($fields['id'])) {
            
            if (! $row = self::find($fields['id'])) {
                throw new \Exception('Pessoa não localizada.');
            }

            $row->name = $fields['name'];
            $row->save();
        } else {

            if (self::find_by_name($fields['name'])) {
                throw new \Exception('Já existe uma pessoa com este nome');
            }

            $row = self::create([
                'name' => $fields['name']
            ]);
        }

        if ($row->is_invalid()) {
            throw new \Exception($row->getFisrtError());
        }

        return $row;
    }

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

    public static function remove($people_id)
    {
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

    public function getLogDescription($action)
    {
        return [
            'create' => "Adicionou '{$this->name}' em pessoas.",
            'update' => "Alterou o nome de '{$this->backup_for_log->name}' para '{$this->name}'.",
            'destroy' => "Apagou '{$this->name}' de pessoas.",
        ][$action];
    }
}
