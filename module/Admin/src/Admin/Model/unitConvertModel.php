<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 4/9/2015
 * Time: 3:07 PM
 */
namespace Admin\Model;
use Zend\InputFilter\InputFilterInterface;

class unitConvertModel extends globalModel{
    function __construct($controller)
    {

        $this->entityName = 'Admin\Entity\ItemUnitConvert';
        parent::__construct($controller);
    }
    public function hydrator($data = array()){

    }
    public  function convertToArray($datas){
        $return = array();
        foreach($datas as $data){
            $array = array();
            $array['id'] = $data->getId();
            $array['unit_item_one'] = $data->getUnitItemOne();
            $array['unit_item_two'] = $data->getUnitItemTwo();
            $array['value'] = $data->getValue();
            $return[] = $array;
        }
        return $return;
    }


}