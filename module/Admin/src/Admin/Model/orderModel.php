<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\Orders;
use Doctrine\ORM\Query\Expr\GroupBy;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;

class orderModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Orders';
        parent::__construct($controller);
    }

    public function hydrator($data = array()){

    }
    public  function convertToArray($datas){

        $return = array();
        foreach($datas as $data){
            $array = $this->convertSingleToArray($data);
            $return[] = $array;
        }
        return $return;
    }

    public function  convertSingleToArray($data){

        $tableInfo = Utility::getTableInfo( $data->getTableid());
        $config = Utility::getConfig();
        $code = '';
        $value = '';
        $desc  = '';

        if($data->getCouponId() != -1 ){
            $coupon = Utility::getCouponInfo($data->getCouponId());
//            echo '<pre>';
//            print_r($coupon);
//            echo '</pre>';
            $code = $coupon->getCode();
            if($coupon->getReuse() == 1)
                $code = $coupon->getDescription();
            $type = $coupon->getType();

            if($type == 0){
                $value = 'Reduce :'.number_format($coupon->getValue()).' '.$config['currency'];
            }else{
                $value = 'Reduce :'.$coupon->getValue().'%';
            }

            $desc = $coupon->getDescription();

        }

        $userInfo = Utility::getUserInfo($data->getUserId());
        $surtax = Utility::getSurTaxInfo($data->getSurtaxId());
        if($surtax){
            $surtaxType = $surtax->getType();
            $surtaxValue = $surtax->getValue();
        }else{
            $surtaxType = 'Cash';
            $surtaxValue = 0;
        }

        $taxType =  Utility::convertSurtaxType($surtaxType);
        //print_r($coupon);
        $array = array();
        $array['id'] = $data->getId();
        $array['tableId'] = $tableInfo->getName();
        $array['createDate'] =  date('d/m/Y h:i:s',$data->getCreateDate());
        $array['totalCost'] = number_format($data->getTotalCost());
        $array['totalRealCost'] = number_format($data->getTotalRealCost());
        $array['coupon'] = $code;
        $array['couponValue'] = ($value);
        $array['couponDesc'] = $desc;
        $array['surtax'] = number_format($surtaxValue) .' '.$taxType;
        $array['userid'] = $userInfo->getUserName();
        $array['status'] = $data->getStatus();
        $array['customer_id'] = $data->getCustomerId();
        $array['newDate'] = $data->getNewDate();
        return $array;

    }

    public function createQuery($strQuery){

        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' count(table) as count_user,
             table.userId,
             SUM(table.totalRealCost) as total_real_cost,
             SUM(table.totalCost) as total_cost ')
            ->where($strQuery)
            ->groupBy('table.userId')
            ->getQuery()
            ->getResult();
        return $rs;

    }

    public function createQueryFindAll($strQuery){

        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' table')
            ->where($strQuery)
            ->getQuery()
            ->getResult();
        return $rs;

    }

    public function createQueryTable($strQuery){
        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' count(table) as count_table, table.tableId')
            ->where($strQuery)
            ->groupBy('table.tableId')
            ->getQuery()
            ->getResult();
        return $rs;
    }


    public function createQueryAllMenu($strQuery,$hasMenuId = false){
        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' count(table) as count_table, sum(table.totalCost) as tCost, sum(table.totalRealCost) as tRCost ')
            ->where($strQuery)
            ->getQuery()
            ->getResult();
        return $rs;
    }

    public function createQueryByMenu($strQuery){
        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' count(table) as count_table, sum(table.totalCost) as tCost, sum(table.totalRealCost) as tRCost ')
            ->from(' Admin\Entity\OrderDetail','od')
            ->from(' Admin\Entity\Menu','mn')
            ->where($strQuery)
            ->getQuery()
            ->getResult();
        return $rs;
    }

    public function reportAllMonth($year){
        $array = array();
        for($i=1; $i<=12; $i++){
           $report = $this->reportMonth($i,$year);
           $array[$i] = $report[0];
        }
        return $array;
    }

    public function reportMonth($moth, $year){
        $date1 = strtotime(date('1-'.$moth.'-'.$year.' 0:0:0'));
        $date2 = strtotime(date('31-'.$moth.'-'.$year.' 23:59:0 '));
        $str = 'c.createDate >= '.$date1.' AND c.createDate <='.$date2;

        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('c');
        $resault = $querybuilder->select('sum(c.totalCost) AS totalCost, sum(c.totalRealCost) AS totalRealCost, count(c) as countOrder  ')

            ->where('c.isdelete = 0 AND '.$str)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
        return $resault;
    }

    public function sumTotalCostByUserPerDay($strQuery){
        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select('sum(table.totalCost) as tCost, sum(table.totalRealCost) as tRCost ')
            ->where($strQuery)
            ->getQuery()
            ->getResult()
        ;

        return $rs;
    }


    public function test(){
        $querybuilder = $this->objectManager
            ->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select ('table')
            ->groupBy('DATE(table.createDate)')
            ->getQuery()
            ->getResult();

        echo '<pre>';
        print_r($rs);
        echo '</pre>';
        die;
    }
}