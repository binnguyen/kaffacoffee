<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\Menu;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;

class menuModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Menu';
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
            $array = $this->convertSingleToArray($data);
            $return[] = $array;
        }
        return $return;
    }

    public function  convertSingleToArray($data){
//        echo '<pre>';
//        print_r($data);
//        echo '</pre>';
        $catInfo = Utility::getCatInfo($data->getCatId());
        $array = array();
        $array['id'] = $data->getId();
        $array['name'] = $data->getName();
        $array['function'] = Utility::getMenuStoreInMenu($data->getId());
        $array['desc'] = $data->getDescription();
        $array['image'] = $data->getImage();
        $array['catId'] = $catInfo->getName();
        $array['cost'] = number_format($data->getCost());
        $array['taCost'] = number_format($data->getTakeAwayCost());
        $array['isCombo'] = Utility::getCombo(number_format($data->getIsCombo()));
        return $array;
    }

}