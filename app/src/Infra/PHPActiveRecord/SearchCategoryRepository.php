<?php 

namespace App\Infra\PHPActiveRecord;

use Domain\Category\Usecase\SearchCategoryRepository as RepositoryInterface;
use Domain\Category\Category;
use Category as CategoryModel;

class SearchCategoryRepository implements RepositoryInterface
{
    public function getCategoryByName($name)
    {
        $object = CategoryModel::find('first', ['conditions' => ['name = ?', $name]]);

        if (!$object) {
            return null;
        }

        $category = new Category($object->name);
        $category->setId($object->id);

        return $category;
    }
}