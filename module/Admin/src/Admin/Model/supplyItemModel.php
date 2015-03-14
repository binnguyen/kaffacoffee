<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use Admin\Entity\SupplierItem;
use stdClass;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;


class supplyItemModel extends globalModel {

    protected $loginUser;
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\SupplierItem';
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
        $array['value'] = $event->getValue();
        $array['id'] = $event->getId();
        return $array;
    }


    public function testSQl($sqlStr){

    }
}