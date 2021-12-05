<?php

namespace Domain\Category\Usecase;

use Domain\Category\Category;

interface AddNewCategoryRepository
{
    public function saveCategory(Category $category);
}