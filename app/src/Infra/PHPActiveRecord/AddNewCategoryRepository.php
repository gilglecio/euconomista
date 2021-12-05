<?php 

namespace App\Infra\PHPActiveRecord;

use Domain\Category\Usecase\AddNewCategoryRepository as RepositoryInterface;
use Domain\Category\Category;
use Category as CategoryModel;

class AddNewCategoryRepository implements RepositoryInterface
{
    public function saveCategory(Category $category)
    {
        $category = CategoryModel::generate([
            'name' => $category->getName()
        ]);

        return $category->id;
    }
}