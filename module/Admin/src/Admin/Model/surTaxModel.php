<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\Surtax;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;

class surTaxModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Surtax';
        parent::__construct($controller);
    }




    public function hydrator($data = array()){
        $user = new Menu();
        $user = $this->hydrator($data,$user);
        return $user->getFullname();
    }

    public  function convertToArray($datas){
        $return = array();
        foreach($datas as $data){
            $array = array();
            $array['id'] = $data->getId();
            $array['name'] = $data->getName();
            $array['value'] = $data->getValue();
            $array['type'] = $data->getType();
            $return[] = $array;
        }
        return $return;
    }



}