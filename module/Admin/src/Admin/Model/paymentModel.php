<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use stdClass;
use Admin\Entity\Payment;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\Query;


class paymentModel extends globalModel {

    protected $loginUser;
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Payment';
        parent::__construct($controller);
    }

    public function hydrator($data = array()){
        $user = new Payment();
        $user = $this->hydrator($data,$user);
        return $user->getTitle();
    }


    public function convertToArray($data){
        $array = array();
        foreach($data as $item){
            $array[] = $this->convertSingleToArray($item);
        }
        return $array;
    }
    public function convertSingleToArray($user){

        $paymentCategory = Utility::getPaymentCateInfo($user->getCategoryId());


        $date = $user->getTime();
        ( $date == '') ? $time = time() : $time = $date;
        $array = array();
        $array['id'] = $user->getId();
        $array['title'] = $user->getTitle();
        $array['value'] = number_format($user->getValue());
        $array['reason'] = $user->getReason();
        $array['time'] =  date('d-m-Y', $time );
        $array['categoryId'] = $user->getCategoryId();
        return $array;

    }

    public function createQuery($strQuery){

        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select('table')
            ->where($strQuery)
            ->getQuery()
            ->getResult();
        return $rs;

    }


    public function createQueryToArray($strQuery){

        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select('table')
            ->where($strQuery)
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY );
        return $rs;

    }


}