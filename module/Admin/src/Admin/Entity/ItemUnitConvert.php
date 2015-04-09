<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ItemUnitConvert
 *
 * @ORM\Table(name="item_unit_convert")
 * @ORM\Entity
 */
class ItemUnitConvert
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="unit_item_one", type="integer", nullable=false)
     */
    private $unitItemOne;

    /**
     * @var integer
     *
     * @ORM\Column(name="unit_item_two", type="integer", nullable=false)
     */
    private $unitItemTwo;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float", precision=10, scale=0, nullable=false)
     */
    private $value;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getUnitItemOne()
    {
        return $this->unitItemOne;
    }

    /**
     * @param int $unitItemOne
     */
    public function setUnitItemOne($unitItemOne)
    {
        $this->unitItemOne = $unitItemOne;
    }

    /**
     * @return int
     */
    public function getUnitItemTwo()
    {
        return $this->unitItemTwo;
    }

    /**
     * @param int $unitItemTwo
     */
    public function setUnitItemTwo($unitItemTwo)
    {
        $this->unitItemTwo = $unitItemTwo;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


}
