<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Orders
 *
 * @ORM\Table(name="orders")
 * @ORM\Entity
 */
class Orders
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
     * @ORM\Column(name="table_id", type="integer", nullable=false)
     */
    private $tableId;

    /**
     * @var integer
     *
     * @ORM\Column(name="create_date", type="bigint", nullable=false)
     */
    private $createDate;

    /**
     * @var float
     *
     * @ORM\Column(name="total_cost", type="float", precision=10, scale=0, nullable=false)
     */
    private $totalCost;

    /**
     * @var float
     *
     * @ORM\Column(name="total_real_cost", type="float", precision=10, scale=0, nullable=false)
     */
    private $totalRealCost;

    /**
     * @var integer
     *
     * @ORM\Column(name="isdelete", type="integer", nullable=false)
     */
    private $isdelete;

    /**
     * @var integer
     *
     * @ORM\Column(name="coupon_id", type="integer", nullable=true)
     */
    private $couponId;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="surtax_id", type="integer", nullable=false)
     */
    private $surtaxId;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=100, nullable=false)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $customerId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="new_date", type="text", nullable=false)
     */
    private $newDate;

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
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * @param int $tableId
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param int $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return float
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param float $totalCost
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }

    /**
     * @return float
     */
    public function getTotalRealCost()
    {
        return $this->totalRealCost;
    }

    /**
     * @param float $totalRealCost
     */
    public function setTotalRealCost($totalRealCost)
    {
        $this->totalRealCost = $totalRealCost;
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
     * @return int
     */
    public function getCouponId()
    {
        return $this->couponId;
    }

    /**
     * @param int $couponId
     */
    public function setCouponId($couponId)
    {
        $this->couponId = $couponId;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getSurtaxId()
    {
        return $this->surtaxId;
    }

    /**
     * @param int $surtaxId
     */
    public function setSurtaxId($surtaxId)
    {
        $this->surtaxId = $surtaxId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return string
     */
    public function getNewDate()
    {
        return $this->newDate;
    }

    /**
     * @param string $newDate
     */
    public function setNewDate($newDate)
    {
        $this->newDate = $newDate;
    }


}
