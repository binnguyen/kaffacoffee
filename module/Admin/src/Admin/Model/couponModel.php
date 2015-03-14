<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use stdClass;
use Admin\Entity\Coupon;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\AuthenticationService;


class couponModel extends globalModel{

    protected $loginUser;
    protected $objectManager;
    function __construct($controller)
    {
        $this->objectManager = $controller;
        $this->entityName = 'Admin\Entity\Coupon';
        parent::__construct($controller);
    }

    public function setLoginUser($user){
        $this->loginUser = $user;
    }

    public function getLoginUser(){
        return $this->loginUser;
    }


    public function hydrator($data = array()){
        $user = new Coupon();
        $user = $this->hydrator($data,$user);
        return $user->getId();
    }


    public function convertToArray($data){
        $array = array();
        foreach($data as $item){
            $array[] = $this->convertSingleToArray($item);
        }
        return $array;
    }

    public function convertSingleToArray($coupon){
        $translator = Utility::translate();
        $couponType = Utility::getCouponType($coupon->getType());
        $array = array();
        $array['id'] = $coupon->getId();
        $array['code'] = $coupon->getCode();
        $array['value'] = $coupon->getValue();
        $array['fromdate'] = date('d/m/Y', $coupon->getFromDate());
        $array['todate'] = date('d/m/Y', $coupon->getToDate());
        $array['type'] = $translator->translate($couponType);
        $array['description'] = $coupon->getDescription();
        return $array;
    }

    public function getAllCoupon($strQuery){

        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' table ')
            ->where($strQuery)
            ->getQuery()
            ->getResult();
        return $rs;

    }

    public function delExpireCoupon(){
        $now = date('d-m-Y',time());
        $nowTime = strtotime($now);
        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' table ')
            ->where('table.todate <'.$nowTime)
            ->getQuery()
            ->getResult();
        return $rs;
    }

}