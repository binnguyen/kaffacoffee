<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 4/9/2015
 * Time: 3:07 PM
 */
namespace Admin\Model;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;

class unitModel extends globalModel{
    function __construct($controller)
    {

        $this->entityName = 'Admin\Entity\ItemUnit';
        parent::__construct($controller);
    }
    public function hydrator($data = array()){

    }
    public  function convertToArray($datas){

        $return = array();
        foreach($datas as $data){
            $convertList = Utility::getUnitConverted($data->getId());
            $convertList = json_encode($convertList);
            $array = array();
            $array['id'] = $data->getId();
            $array['name'] = $data->getName();
            $array['converted'] = $convertList;
            $return[] = $array;
        }
        return $return;
    }


}