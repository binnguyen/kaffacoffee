<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\MenuCombo;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;

class comboModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\MenuCombo';
        parent::__construct($controller);
    }


    public function hydrator($data = array()){
        $user = new MenuCombo();
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
        $parentMenu = Utility::getMenuInfo($data->getMenuParentId());
        $childMenu = Utility::getMenuInfo($data->getMenuChildId());
        $array = array();
        $array['id'] = $data->getId();
        $array['menu_parent_id'] = $parentMenu->getName();
        $array['menu_child_id'] = $childMenu->getName();
        $array['menu_quantity'] = $data->getMenuQuantity();
        $array['menu_cost'] = $childMenu->getCost();
        $array['menu_ta_cost'] = $childMenu->getTakeAwayCost();
        $array['menu_total_cost'] = $childMenu->getCost()*$data->getMenuQuantity();
        $array['menu_total_ta_cost'] = $childMenu->getTakeAwayCost()*$data->getMenuQuantity();

        return $array;
    }



}