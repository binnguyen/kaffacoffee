<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use Admin\Entity\SupplierFor;
use stdClass;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;


class supplyForModel extends globalModel {

    protected $loginUser;
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\SupplierFor';
        parent::__construct($controller);
    }





    public function hydrator($data = array()){
//        $user = new Config();
//        $user = $this->hydrator($data,$user);
//        return $user->getFullname();
    }


    public function convertToArray($data){
        $array = array();
        foreach($data as $item){
            $array[] = $this->convertSingleToArray($item);
        }
        return $array;
    }
    public function convertSingleToArray($event){
        $array = array();
        $array['name'] = $event->getName();
        $array['id'] = $event->getId();
        return $array;
    }


    public function testSQl($sqlStr){

    }
}