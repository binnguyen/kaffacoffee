<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\Managetable;
use Zend\InputFilter\InputFilterInterface;

class tableModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Managetable';
        parent::__construct($controller);
    }
    public function hydrator($data = array()){
        $user = new Managetable();
        $user = $this->hydrator($data,$user);
        return $user->getName();
    }

    public  function convertToArray($datas){
        $return = array();
        foreach($datas as $data){
            $array = array();
            $array['id'] = $data->getId();
            $array['name'] = $data->getName();
            $array['isdelete'] = $data->getIsdelete();
            $return[] = $array;
        }
        return $return;
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


}