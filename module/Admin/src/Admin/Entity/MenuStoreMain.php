<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuStoreMain
 *
 * @ORM\Table(name="menu_store_main", indexes={@ORM\Index(name="name", columns={"name"})})
 * @ORM\Entity
 */
class MenuStoreMain
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
     * @var float
     *
     * @ORM\Column(name="cost", type="float", precision=10, scale=0, nullable=false)
     */
    private $cost;

    /**
     * @var integer
     *
     * @ORM\Column(name="isdelete", type="integer", nullable=false)
     */
    private $isdelete;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=500, nullable=false)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="out_of_stock", type="float", precision=10, scale=0, nullable=false)
     */
    private $outOfStock;

    /**
     * @var integer
     *
     * @ORM\Column(name="supplier", type="integer", nullable=false)
     */
    private $supplier;

    /**
     * @var integer
     *
     * @ORM\Column(name="supply_item", type="integer", nullable=false)
     */
    private $supplyItem;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="text", nullable=false)
     */
    private $unit;

    /**
     * @var string
     *
     * @ORM\Column(name="des", type="text", nullable=false)
     */
    private $des;

    /**
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return string
     */
    public function getDes()
    {
        return $this->des;
    }

    /**
     * @param string $des
     */
    public function setDes($des)
    {
        $this->des = $des;
    }

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
    public function getIsdelete()
    {
        return $this->isdelete;
    }

    /**
     * @param int $isdelete
     */
    public function setIsdelete($isdelete)
    {
        $this->isdelete = $isdelete;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getOutOfStock()
    {
        return $this->outOfStock;
    }

    /**
     * @param float $outOfStock
     */
    public function setOutOfStock($outOfStock)
    {
        $this->outOfStock = $outOfStock;
    }

    /**
     * @return int
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param int $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return int
     */
    public function getSupplyItem()
    {
        return $this->supplyItem;
    }

    /**
     * @param int $supplyItem
     */
    public function setSupplyItem($supplyItem)
    {
        $this->supplyItem = $supplyItem;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }


}
