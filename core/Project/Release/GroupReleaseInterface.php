<?php

namespace Project\Release;

use Project\Person\PersonInterface;
use Project\Category\CategoryInterface;

interface GroupReleaseInterface
{
    /**
     * @param ReleaseInterface $release
     */
    public function __construct(ReleaseInterface $release);

    public function setPerson(PersonInterface $person);

    public function setCategory(CategoryInterface $category);

    public function setDueDate($due_date);

    public function setNumber($number);

    public function setDescription($description);

    /**
     * @return ReleaseInterface
     */
    public function appendRelease(ReleaseInterface $release);

    /**
     * @return boolean
     */
    public function isValid();
}
