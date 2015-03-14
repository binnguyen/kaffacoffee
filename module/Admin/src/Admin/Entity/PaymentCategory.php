<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentCategory
 *
 * @ORM\Table(name="payment_category")
 * @ORM\Entity
 */
class PaymentCategory
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
     * @ORM\Column(name="isdelete", type="integer", nullable=false)
     */
    private $isdelete = '0';

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


}
