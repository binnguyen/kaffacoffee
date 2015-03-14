<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrackingTore
 *
 * @ORM\Table(name="tracking_tore")
 * @ORM\Entity
 */
class TrackingTore
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="supplier_item_id", type="integer", nullable=true)
     */
    private $supplierItemId;

    /**
     * @var string
     *
     * @ORM\Column(name="supplier_item_name", type="string", length=255, nullable=true)
     */
    private $supplierItemName;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="string", length=255, nullable=true)
     */
    private $note;

    /**
     * @var string
     *
     * @ORM\Column(name="time", type="string", length=100, nullable=true)
     */
    private $time;

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
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getSupplierItemId()
    {
        return $this->supplierItemId;
    }

    /**
     * @param int $supplierItemId
     */
    public function setSupplierItemId($supplierItemId)
    {
        $this->supplierItemId = $supplierItemId;
    }

    /**
     * @return string
     */
    public function getSupplierItemName()
    {
        return $this->supplierItemName;
    }

    /**
     * @param string $supplierItemName
     */
    public function setSupplierItemName($supplierItemName)
    {
        $this->supplierItemName = $supplierItemName;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }


}
