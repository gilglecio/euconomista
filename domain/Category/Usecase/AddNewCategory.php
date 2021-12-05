<?php

namespace Domain\Category\Usecase;

use Domain\Category\Category;

use Domain\Category\Error\CategoryException;

class AddNewCategory
{
    private $saveCategory;
    private $searchCategory;

    public function __construct(
        AddNewCategoryRepository $saveCategory,
        SearchCategoryRepository $searchCategory
    ) {
        $this->saveCategory = $saveCategory;
        $this->searchCategory = $searchCategory;
    }

    public function handle(CategoryInput $data)
    {
        if ($this->searchCategory->getCategoryByName($data->getName())) {
            throw new CategoryException('Já existe uma categoria com este nome');
        }

        $category = new Category($data->getName());

        $category_id = $this->saveCategory->saveCategory($category);

        return $category_id;
    }
}