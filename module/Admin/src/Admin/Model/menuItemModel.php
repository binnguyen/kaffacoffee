<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 10/6/2014
 * Time: 3:04 PM
 */

namespace Admin\Model;


use Admin\Entity\MenuItem;
use Zend\InputFilter\InputFilterInterface;

class menuItemModel extends globalModel {
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\MenuItem';
        parent::__construct($controller);
    }
    public function hydrator($data = array()){
//        $user = new tran();
//        $user = $this->hydrator($data,$user);
//        return $user->getFullname();
    }
    public  function convertToArray($datas){
//        $return = array();
//        foreach($datas as $data){
//            $array = array();
//            $array['id'] = $data->getId();
//            $array['name'] = $data->getName();
//            $return[] = $array;
//        }
//        return $return;
    }

    public function getMenuItemInStore($strQuery){
//        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
//        $rs = $querybuilder
//            ->select(' table, sum(table.quantity) as count_menu, table.menuId, sum(table.realCost) as realCost')
//            ->from(' Admin\Entity\MenuStore','ms')
//            ->where($strQuery.' AND ms.id = table.menuStoreId AND ms.')
//            ->groupBy('table.menuId')
//            ->getQuery()
//            ->getResult();
//
//        return $rs;

        //get all item of Menu

    }
}