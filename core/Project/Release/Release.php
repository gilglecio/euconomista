<?php

namespace Project\Release;

use Project\Person\PersonInterface;
use Project\Category\CategoryInterface;

/**
 * Release class
 */
class Release implements ReleaseInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $number;

    /**
     * @var float
     */
    private $value;

    /**
     * @var integer
     */
    private $nature;

    /**
     * @var string
     */
    private $due_date;

    /**
     * @var PersonInterface
     */
    private $person;

    /**
     * @var CategoryInterface
     */
    private $category;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Release
     */
    private $parent_release;

    /**
     * @var string
     */
    private $process_number;

    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 2;
    const STATUS_LATE = 3;
    const STATUS_GROUPED = 4;
    const STATUS_IN_INSTALLMENT = 5;

    const NATIRE_RECIPE = 1;
    const NATURE_EXPENSE = 2;

    /**
     * Arrow the launch id.
     *
     * @param integer $id Id of release
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId($id)
    {
        return $this->id;
    }

    /**
     * Arrow the launch number.
     *
     * @param string $number Number of release
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function getNumber($number)
    {
        return $this->number;
    }
    
    /**
     * Arrow the launch value.
     *
     * @param float $value Value of release
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue($value)
    {
        return $this->value;
    }
    
    /**
     * Arrow the launch nature.
     *
     * @param integer $nature Nature of release
     */
    public function setNatureza($nature)
    {
        $this->nature = $nature;
    }

    public function getNatureza($nature)
    {
        return $this->nature;
    }
    
    /**
     * Arrow the launch due date.
     *
     * @param \Datetime $due_date Due date of release
     */
    public function setDueDate($due_date)
    {
        $this->due_date = new \Datetime($due_date);
    }

    public function getDueDate($due_date)
    {
        return $this->due_date;
    }
    
    /**
     * Arrow the launch person.
     *
     * @param PersonInterface $person Person of release
     */
    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;
    }

    public function getPerson(PersonInterface $person)
    {
        return $this->person;
    }
    
    /**
     * Arrow the launch category.
     *
     * @param CategoryInterface $category Category of release
     */
    public function setCategory(CategoryInterface $category)
    {
        $this->category = $category;
    }

    public function getCategory(CategoryInterface $category)
    {
        return $this->category;
    }
    
    /**
     * Arrow the launch description.
     *
     * @param string $description Description of release
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription($description)
    {
        return $this->description;
    }
    
    /**
     * Arrow the launch parent.
     *
     * @param Release $parent_release Parent of release
     */
    public function setParentRelease(Release $parent_release)
    {
        $this->parent_release = $parent_release;
    }

    public function getParentRelease(Release $parent_release)
    {
        return $this->parent_release;
    }
    
    /**
     * Arrow the launch number.
     *
     * @param string $number Number of release
     */
    public function setProcessNumber($process_number)
    {
        $this->process_number = $process_number;
    }

    public function getProcessNumber($process_number)
    {
        return $this->process_number;
    }
}
