<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SupplierFor
 *
 * @ORM\Table(name="supplier_for")
 * @ORM\Entity
 */
class SupplierFor
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
     * @ORM\Column(name="suppiler_id", type="integer", nullable=false)
     */
    private $suppilerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="supplier_item", type="integer", nullable=false)
     */
    private $supplierItem;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $suppilerId
     */
    public function setSuppilerId($suppilerId)
    {
        $this->suppilerId = $suppilerId;
    }

    /**
     * @return int
     */
    public function getSuppilerId()
    {
        return $this->suppilerId;
    }

    /**
     * @param int $supplierItem
     */
    public function setSupplierItem($supplierItem)
    {
        $this->supplierItem = $supplierItem;
    }

    /**
     * @return int
     */
    public function getSupplierItem()
    {
        return $this->supplierItem;
    }


}
