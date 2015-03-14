<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use stdClass;
use Admin\Entity\PaymentCategory;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\Query;


class paymentCategoryModel extends globalModel {

    protected $loginUser;
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\PaymentCategory';
        parent::__construct($controller);
    }

    public function hydrator($data = array()){
        $user = new PaymentCategory();
        $user = $this->hydrator($data,$user);
        return $user->getName();
    }


    public function convertToArray($data){
        $array = array();
        foreach($data as $item){
            $array[] = $this->convertSingleToArray($item);
        }
        return $array;
    }
    public function convertSingleToArray($user){
        $array = array();
        $array['id'] = $user->getId();
        $array['name'] = $user->getName();
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