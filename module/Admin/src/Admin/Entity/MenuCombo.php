<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuCombo
 *
 * @ORM\Table(name="menu_combo")
 * @ORM\Entity
 */
class MenuCombo
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
     * @ORM\Column(name="menu_parent_id", type="integer", nullable=false)
     */
    private $menuParentId;

    /**
     * @var integer
     *
     * @ORM\Column(name="menu_child_id", type="integer", nullable=false)
     */
    private $menuChildId;

    /**
     * @var integer
     *
     * @ORM\Column(name="menu_quantity", type="integer", nullable=false)
     */
    private $menuQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="isdelete", type="integer", nullable=false)
     */
    private $isdelete;

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
     * @param int $isdelete
     */
    public function setIsdelete($isdelete)
    {
        $this->isdelete = $isdelete;
    }

    /**
     * @return int
     */
    public function getIsdelete()
    {
        return $this->isdelete;
    }

    /**
     * @param int $menuChildId
     */
    public function setMenuChildId($menuChildId)
    {
        $this->menuChildId = $menuChildId;
    }

    /**
     * @return int
     */
    public function getMenuChildId()
    {
        return $this->menuChildId;
    }

    /**
     * @param int $menuParentId
     */
    public function setMenuParentId($menuParentId)
    {
        $this->menuParentId = $menuParentId;
    }

    /**
     * @return int
     */
    public function getMenuParentId()
    {
        return $this->menuParentId;
    }

    /**
     * @param int $menuQuantity
     */
    public function setMenuQuantity($menuQuantity)
    {
        $this->menuQuantity = $menuQuantity;
    }

    /**
     * @return int
     */
    public function getMenuQuantity()
    {
        return $this->menuQuantity;
    }


}
