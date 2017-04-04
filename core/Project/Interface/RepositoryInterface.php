<?php

namespace Project\interface;

interface RepositoryInterface
{
    public function findById($id);

    public function findAll();

    public function insert($fields);

    public function updateById($id, $fields);

    public function deleteById($id);
}
