<?php

namespace EuConomista\Category;

interface CategoryInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * Set category id.
     *
     * @param integer $id Category id
     * @return void
     */
    public function setId($id);

    /**
     * Get category id.
     *
     * @return integer Id of category
     */
    public function getId();

    /**
     * Set category id.
     *
     * @param string $name Name of category
     * @return void
     */
    public function setName($name);

    /**
     * Set category id.
     *
     * @return string Name of category
     */
    public function getName();
}
