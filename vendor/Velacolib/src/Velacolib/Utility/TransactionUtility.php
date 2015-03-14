<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 10/6/2014
 * Time: 2:25 PM
 */

namespace Velacolib\Utility;


use Admin\Entity\MenuItem;
use Admin\Entity\Transaction;
use Admin\Model\menuItemModel;
use Admin\Model\menuStoreModel;
use Admin\Model\transactionModel;
use Zend\Mvc\Controller\AbstractActionController;

class TransactionUtility  extends AbstractActionController {
    public static $option;
    public static $servicelocator;

    public static function getSM()
    {
        return self::$servicelocator;
    }

    public static function setSM($val)
    {
        self::$servicelocator = $val;
    }

    public static  function checkStore($menuStoreId,$type, $store='sub'){
        $doctrine = self::$servicelocator->get('doctrine');
        $transactionModel = new transactionModel($doctrine);
        $menuItem = $transactionModel->checkStore($menuStoreId, $type,$store);
        return $menuItem;
    }


   //import export menu order item
    public static function insertTransaction($data,$supplier = 0,$cost=0, $store='sub'){

        $doctrine = self::$servicelocator->get('doctrine');
        $transactionModel = new transactionModel($doctrine);
        //$transactionModel->begin();
        $transaction = new Transaction();
        $transaction->setMenuStoreId($data['menuStoreId']);
        //create function get menuItem quantity
        $transaction->setQuantity($data['quantity']);
        $transaction->setAction($data['action']);
        $transaction->setDate(time());
        //create function get menuItem quantity
        $transaction->setUnit($data['unit']);
        $transaction->setNote($data['note']);
        $transaction->setCost($cost);
        $transaction->setSupplier($supplier);
        $transaction->setStore($store);
        if(isset($data['orderId'])){
            $transaction->setOrderId($data['orderId']);
        }else{
            $transaction->setOrderId(0);
        }
        //insert transaction
        $transactionModel->insert($transaction);
        //$transactionModel->commit();
    }

    /**
     * @param $menuId
     * @param int $type
     * @return mixed
     * $type = -1 Add Order
     * $type = 1 Insert into store
     */

    public static  function updateQuantityMenuItemInStore($menuId,$orderQuantity, $type, $action, $note = '',$orderId  = 0 ){
        $doctrine = self::$servicelocator->get('doctrine');
        $menuItemModel = new menuItemModel($doctrine);
        $menuItems = $menuItemModel->findBy(array('menuId'=>$menuId));

        foreach($menuItems as $menuItem){

            $menuStoreId = $menuItem->getMenuStoreId();
            $menuStoreIdQuantity = $menuItem->getQuantity();

            $menuId = $menuItem->getMenuId();
            //insert transaction in menuID
            $data['menuStoreId'] = $menuStoreId;
            $data['quantity'] = $menuStoreIdQuantity*$type*$orderQuantity;
            $data['action'] = $action;
            $data['unit'] = $menuItem->getUnit();
            $data['menuId'] = $menuId;
            $data['note'] = $note;
            $data['orderId'] = $orderId;

            self::insertTransaction($data);
        }
    }

    public static function  checkAndSendNotifyEmail(){
        $config = Utility::getConfig();
        $translator = Utility::translate();
        $doctrine = self::$servicelocator->get('doctrine');
        $menuStoreModel = new menuStoreModel($doctrine);

        $menuStore = $menuStoreModel->findAll();

        foreach($menuStore as $store){

            $store = $menuStoreModel->findOneBy(array('id'=>$store->getId()));

            if($store->getOutOfStock() == -1){
                $outOfStock = $config['out_of_stock'];
            }else{
                $outOfStock = $store->getOutOfStock();
            }
            $compare =   self::getMenuItemQuantityInStore($store->getId());
            $data = array(
                'name'=> $store->getName(),
                'inStore'=> $compare,
                'unit'=>$store->getUnit(),
            );

            $subject = $translator->translate('Out of stock');
            $receiveEmail = $config['emailId'];
            if($compare < $outOfStock){
                Utility::sendEmail('emptystore',$data,$subject,$receiveEmail,true);
            }

        }


    }


    public static function getMenuItemQuantityInStore($storeId,$store='sub'){
        $doctrine = self::$servicelocator->get('doctrine');
        $transactionModel = new transactionModel($doctrine);
        $sumTransaction = $transactionModel->createQueryGetSumStoreItem($storeId,$store);
        return $sumTransaction[0]['sum_store'];
    }
    //end import export menu order item

    public static  function getStoreItemInOrder($data){
        $item = json_decode($data,true);
        $html = '';
        if($item){


                $html .= '<p> <a target="_blank" href="/admin/order/detail/'.$item['orderID'].'"> Order:'.$item['orderID'].'- Order Detail:'.$item['orderDetailId'].' </p><br/>';


            return $html;
        }
        return '';

    }

    /*
     * @param $menuStoreId
     * insert storeId
     * get supplier + cost
     */
    public function sortCostTransaction($menuStoreId){
        $doctrine = self::$servicelocator->get('doctrine');
        $transactionModel = new transactionModel($doctrine);
        $transaction = $transactionModel->findBy(array(
            'action' => 'N',
            'menuStoreId' => $menuStoreId
        ));
        $array = array();
        foreach($transaction as $item){
            $cost = $item ->getCost();
            $quantity = $item ->getQuantity();
            $supplier = $item->getSupplier();
            $unit= $item->getUnit();
            $info = array(
                'supplier_id' => $supplier,
                'cost' => $cost,
                'quantity' => $quantity,
                'rating' => self::exChangeCost($cost,$quantity),
                'unit' => $unit,
            );
            $array[] = $info;
        }
        return $array;
    }

    public function exChangeCost($cost, $quantity){
//        echo '-----------------------<br/>';
//        echo $cost;
//        echo '<br/>';
//        echo $quantity;
//        echo '-----------------------<br/>';

        if($cost && $cost !=''  && $quantity != 0 && $quantity != '')
            return floatval($cost)/$quantity;
        return 0;
    }

    /**
     * @param $menuStoreId
     * @return array
     */
    public function sortSupplier($menuStoreId)
    {

        $arraySuplier = self::sortCostTransaction($menuStoreId);
        for($i=0; $i<= count($arraySuplier) - 2; $i++)
        {
            for($j=$i+1; $j<= count($arraySuplier) - 1; $j++)
            {

                if($arraySuplier[$i]['rating'] > $arraySuplier[$j]['rating']){
                    $temp = $arraySuplier[$i];
                    $arraySuplier[$i] = $arraySuplier[$j];
                    $arraySuplier[$j] = $temp;
                }
            }
        }
        return $arraySuplier;
    }

    public static function  renderBestCostSupplier($costBetterSupplier){

        $setting = Utility::getConfig();
//        print_r($setting);

        $contactInfo = '';
        $checkExists = array();
        foreach($costBetterSupplier as $supplierItem){
            $supplierInfo =  \Velacolib\Utility\Utility::getSupplierInfo($supplierItem['supplier_id']);
            if($supplierInfo->getIsdelete() == 0){
                if(!isset($checkExists[$supplierInfo->getId()])){
                    $contactInfo .= '<b>'.$supplierInfo->getCompanyName(). ' - '.$supplierItem['rating'].' '.$setting['currency'].'/'.$supplierItem['unit'].' </b><br/> - Phone: '.$supplierInfo->getPhone().'<br/> - Mobile: ' .$supplierInfo->getMobile().'<br/> - Addr: '.$supplierInfo->getAddr().'<br/><br/>';
                    $checkExists[$supplierInfo->getId()] = $supplierInfo->getId();
                }
            }
            //}
        }
        return $contactInfo;

    }

    public static function rollbackTransaction($orderId){
        $doctrine = self::$servicelocator->get('doctrine');
        $transactionModel = new transactionModel($doctrine);
        $transactionModel->delete(array('orderId'=>$orderId));
        return true;
    }


}