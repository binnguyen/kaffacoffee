<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 10/6/2014
 * Time: 3:04 PM
 */

namespace Admin\Model;


use Admin\Entity\Transaction;
use Velacolib\Utility\TransactionUtility;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;

class transactionModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Transaction';
        parent::__construct($controller);
    }


    public function hydrator($data = array()){
//        $user = new tran();
//        $user = $this->hydrator($data,$user);
//        return $user->getFullname();
    }

    public  function convertToArray($datas,$store = SUB_STORE){
        $return = array();
        foreach($datas as $data){
            $storeInfo = Utility::getStoreInfo( $data->getMenuStoreId());
            if($store == MAIN_STORE){
                $storeInfo = Utility::getMainStoreInfo( $data->getMenuStoreId());

            }


            $note = TransactionUtility::getStoreItemInOrder($data->getNote());
            if($note == ''){
                $note = $data->getNote();
            }
            $supplier  = Utility::getSupplierInfo($data->getSupplier());

            $array = array();
            $array['id'] = $data->getId();
            $array['menuStoreId'] = $storeInfo->getName();
            $array['action'] = $data->getAction();
            $array['quantity'] = $data->getQuantity();
            $array['unit'] = $data->getUnit();
            $array['date'] = date('d-m-Y',$data->getDate());
            $array['note'] =  $note;
            $array['cost'] = $data->getCost();
            $array['supplier'] = $supplier->getCompanyName() ;
            $return[] = $array;
        }
        return $return;
    }


    public function createQueryGetSumStoreItem($storeId,$store=SUB_STORE){

        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' sum(table.quantity) as sum_store, table.menuStoreId')
            ->where('table.menuStoreId ='.$storeId.' AND table.store = \''.$store.'\'')
            ->getQuery()
            ->getResult();
        return $rs;

    }

    public function checkStore($storeId, $type,$store=SUB_STORE){
        $querybuilder = $this->objectManager->getRepository($this->entityName)->createQueryBuilder('table');
        $rs = $querybuilder
            ->select(' sum(table.quantity) as sum_store, table.menuStoreId')
            ->where('table.menuStoreId ='.$storeId.' AND table.action = \''.$type.'\' AND table.store = \''.$store.'\' ')
            ->groupBy('table.menuStoreId')
            ->getQuery()
            ->getResult();
        return $rs;

    }



}