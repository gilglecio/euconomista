<?php

namespace EuConomista\Person;

interface PersonInterface
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
     * Set person id.
     *
     * @param integer $id Person id
     * @return void
     */
    public function setId($id);

    /**
     * Get person id.
     *
     * @return integer Id of person
     */
    public function getId();

    /**
     * Set person id.
     *
     * @param string $name Name of person
     * @return void
     */
    public function setName($name);

    /**
     * Set person id.
     *
     * @return string Name of person
     */
    public function getName();
}
