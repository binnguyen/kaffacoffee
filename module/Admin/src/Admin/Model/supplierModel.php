<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use Admin\Entity\Supplier;
use stdClass;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;


class supplierModel extends globalModel {

    protected $loginUser;
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Supplier';
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
        $arr =  Utility::getSupplyItemOfSupplier($event->getId());
        $html = '';
        foreach($arr as $item){
            $html .=$item['name']."<br/><br/>";
        }
        $array = array();
        $array['phone'] = $event->getPhone();
        $array['id'] = $event->getId();
        $array['mobile'] = $event->getMobile();
        $array['addr'] = $event->getAddr();
        $array['company'] = $event->getCompanyName();
        $array['contact'] = $event->getContactName();
        $array['email'] = $event->getEmail();
       // $array['for'] = $event->getSuplierFor();
        $array['for'] = $html;
        return $array;
    }


    public function testSQl($sqlStr){

    }
}