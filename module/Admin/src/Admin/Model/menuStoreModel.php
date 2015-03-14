<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/6/14
 * Time: 1:58 PM
 */

namespace Admin\Model;
use Admin\Entity\MenuStore;
use Velacolib\Utility\TransactionUtility;
use Velacolib\Utility\Utility;

class menuStoreModel extends globalModel {
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\MenuStore';
        parent::__construct($controller);
    }
    public function hydrator($data = array()){
        $menuStore = new MenuStore();
        $menuStore = $this->hydrator($data,$menuStore);
        return $menuStore->getId();
    }

    public  function convertToArray($datas){


        $return = array();
        foreach($datas as $data){
            $return[] = $this->convertSingleToArray($data);
        }
        return $return;
    }
    public  function convertSingleToArray($data){
        $array = array();
        $input = TransactionUtility::checkStore($data->getId(),INSERT_STORE_ACRION);
        if(isset($input[0])){
            $input = $input[0]['sum_store'];
        }else{ $input = 0; }

        $output =  TransactionUtility::checkStore($data->getId(),ADD_ORDER_ACTION);
        if(isset($output[0])){ $output = $output[0]['sum_store'];  }else{$output = 0;}

        $supplier = Utility::getSupplierInfo($data->getSupplier());

        $array['id'] = $data->getId();
        $array['name'] = $data->getName();
        $array['quantityInput'] = number_format($input);
        $array['quantityOutput'] =  number_format($output);
        $array['quantityInStock'] = TransactionUtility::getMenuItemQuantityInStore( $data->getId());
        $array['unit'] = $data->getUnit();
        $array['outOfStock'] = $data->getOutOfStock();
        $array['des'] = $data->getDes();
        $array['cost'] = $data->getCost();
        $array['supplier'] = $supplier->getCompanyName();
        $array['supplyType'] = $data->getSupplyItem();
        $array['InMenu'] = Utility::getMenuInMenuStore($data->getId());

        return $array;
    }
} 