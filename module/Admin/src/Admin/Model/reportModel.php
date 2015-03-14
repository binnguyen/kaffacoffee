<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\Orders;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;
use Admin\Entity\Payment;

class reportModel {

    public static  function convertUserReportArray($data){
        foreach($data as $k => $val){

            $userInfo = Utility::getUserInfo($val['userId']);
            $data[$k]['userId'] = $userInfo->getUserName();
            $data[$k]['id'] = $k;
        }
        return $data;
    }

    public static  function convertTableReportArray($data){
        foreach($data as $k => $val){

            $tableInfo = Utility::getTableInfo($val['tableId']);
            $data[$k]['tableId'] = $tableInfo->getName();
            $data[$k]['id'] = $k;
        }
        return $data;
    }

    public static  function convertMenuReportArray($data){
        foreach($data as $k => $val){
            $menuInfo = Utility::getMenuInfo($val['menuId']);
            $data[$k]['orderDetailId'] =  $val[0]->getId();
            $data[$k]['OrderId'] = $menuInfo->getId();
            $data[$k]['menuName'] = $menuInfo->getName();
            $data[$k]['realCost'] = $data[$k]['realCost'];
            $data[$k]['time'] = $val[0]->getTime();
            $data[$k]['id'] = $k;
        }
        return $data;
    }

    public static  function   convertMenuTypeReportArray($data){
        foreach($data as $k => $val){
            $data[$k]['costType'] = Utility::getMenuCostType($data[$k]['costType']);
            $data[$k]['realCost'] = number_format($data[$k]['realCost'] );
            $data[$k]['id'] = $k;
        }
        return $data;
    }

    public static  function   convertPaymenttArray($data){
        $d = array();
        foreach($data as $k => $val){
            $d[$k]['id'] = $val->getId();
            $d[$k]['title'] = $val->getTitle();
            $d[$k]['value'] = $val->getValue();
            $d[$k]['reason'] = $val->getReason();
            $d[$k]['time'] = $val->getTime();

        }
        return $d;
    }

}