<?php

namespace Domain\Category\Usecase;

interface SearchCategoryRepository
{
    public function getCategoryByName($name);
}