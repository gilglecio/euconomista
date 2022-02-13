<?php

final class Category extends Model
{
    public static $colors = [
        '000000' => 'Preto', 
        'ff0000' => 'Vermelho', 
        '00ff00' => 'Verde', 
        '0000ff' => 'Azul'
    ];

    public static $validates_presence_of = [
        ['name', 'message' => 'Favor informar o nome da categoria']
    ];

    public static $validates_uniqueness_of = [
        [['name', 'entity'], 'message' => 'Já existe uma categoria com este nome']
    ];

    public static $validates_length_of = [
        ['name', 'within' => [3, 25], 'message' => 'O nome da categoria deve está entre 03 e 25 caracteres'],
        ['hexcolor', 'is' => 6, 'allow_blank' => true]
    ];

    public static $has_many = [
        ['releases']
    ];

    public static function generate($fields)
    {
        if (isset($fields['id']) && is_numeric($fields['id'])) {
            
            if (! $row = self::find($fields['id'])) {
                throw new \Exception('Categoria não localizada.');
            }

            $row->name = $fields['name'];
            $row->hexcolor = $fields['hexcolor'];
            $row->save();
        } else {

            if (self::find_by_name($fields['name'])) {
                throw new \Exception('Já existe uma categoria com este nome');
            }

            $row = self::create([
                'name' => $fields['name'],
                'hexcolor' => $fields['hexcolor'],
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

    public function getColor()
    {
        return $this->hexcolor ? '#' . $this->hexcolor : '#cccccc';
    }

    public static function remove($category_id)
    {
        if (! $category = self::find($category_id)) {
            throw new \Exception('Categoria não localizada.');
        }

        try {
            $category->inUsed();
        } catch (\Exception $e) {
            throw $e;
        }

        if (! $category->delete()) {
            throw new \Exception("Categoria #{$category_id} não foi apagada.");
        }

        return true;
    }

    public function getLogDescription($action)
    {
        return [
            'create' => "Criou a categotia '{$this->name}'.",
            'update' => "Alterou o nome da categoria '{$this->backup_for_log->name}' para '{$this->name}'.",
            'destroy' => "Apagou a categoria '{$this->name}'.",
        ][$action];
    }
}
