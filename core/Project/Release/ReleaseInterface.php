<?php

namespace Project\Release;

use Project\Person\PersonInterface;
use Project\Category\CategoryInterface;

/**
 * ReleaseInterface class
 */
class ReleaseInterface
{
    public function setId($id);

    public function getId($id);

    public function setNumber($number);

    public function getNumber($number);
    
    public function setValue($value);

    public function getValue($value);
    
    public function setNatureza($nature);

    public function getNatureza($nature);
    
    public function setDueDate($due_date);
    
    public function getDueDate($due_date);

    public function setPerson(PersonInterface $person);

    public function getPerson(PersonInterface $person);
    
    public function setCategory(CategoryInterface $category);

    public function getCategory(CategoryInterface $category);

    public function setDescription($description);

    public function getDescription($description);

    public function setParentRelease(Release $parent_release);

    public function getParentRelease(Release $parent_release);

    public function setProcessNumber($process_number);

    public function getProcessNumber($process_number);
}
