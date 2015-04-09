<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/6/14
 * Time: 1:56 PM
 */

namespace Admin\Controller;

use Velacolib\Utility\Table;
use Velacolib\Utility\Table\AjaxTable;
use Velacolib\Utility\Table\Detail;

use Admin\Entity\MenuStore;
use Admin\Model\transactionModel;
use Velacolib\Utility\TransactionUtility;
use Zend\Mvc\Controller\AbstractActionController;
use Velacolib\Utility\Utility;
use Velacolib\Utility\UnitCalcUtility;
use Admin\Model\menuStoreModel;
use Zend\View\Model\ViewModel;


use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class MenustoreController extends AdminGlobalController
{

    protected $menuStoreModel;
    protected $transactionModel;
    protected $translator;
    protected $config;



    public function init(){

        parent::init();
        $this->menuStoreModel = new menuStoreModel($this->doctrineService);
        $this->transactionModel = new transactionModel($this->doctrineService);
    }

    public function indexAction()
    {

        $columns = array(

            array('title' =>'ID', 'db' => 'id', 'dt' => 0, 'select'=>'id','prefix'=>'m','search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1,'select'=>'name','prefix'=>'m', 'search'=>true, 'type' => 'text' ),
            array('title' =>'In', 'db' => 'id','dt' => 2, 'search'=>false, 'type' => 'number','formatter'=>function($d,$row){
                $quantityInput = TransactionUtility::checkStore($d,INSERT_STORE_ACRION);
                if(isset($quantityInput[0])){
                    $input = $quantityInput[0]['sum_store'];
                }else{ $input = 0; }
                return $input;
            }),
            array('title' =>'Out', 'db' => 'id','dt' => 3, 'search'=>true, 'type' => 'number','formatter'=>
                function($d,$row){
                    $quantityOut = TransactionUtility::checkStore($d,ADD_ORDER_ACTION);
                    if(isset($quantityOut[0])){ $output = $quantityOut[0]['sum_store'];  }else{$output = 0;}
                    return $output;
                }
            ),
            array('title' =>'In Stock', 'db' => 'id','dt' => 4, 'search'=>true, 'type' => 'number','formatter'=>
                function($d,$row){

                 return     TransactionUtility::getMenuItemQuantityInStore($d);

                } ),

            array('title' =>'Unit', 'db' => 'unit','dt' => 5 , 'select'=>'unit','prefix'=>'m','search'=>false, 'type' => 'text' ),

            array('title' =>'Supply item', 'db' => 'id','dt' => 6, 'search'=>true, 'type' => 'text'),

            array('title' =>'Action','db'=>'id','dt' => 7 , 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/menustore';
                    $actionTransactionUrl = '/admin/transaction';
                    return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                          <a data-id="'.$d.'" id="'.$d.'" href="'.$actionTransactionUrl.'/inserttransaction/'.$d.'" class="btn-xs action action-detail btn btn-danger"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>

                    ';

                }
            ),


        );


        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/menustore');
        $table->setTablePrefix('m');


        $table->setAjaxCall('/admin/menustore/index');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->menuStoreModel);
        //end config table


        return new ViewModel(array(
            'table' => $table,
            'title' => $this->translator->translate('Manage Inventory')));


    }

    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if ($id == '') {
            if ($request->isPost()) {
                $data = $this->params()->fromPost();

                //check name
                // if exists update
                $name = $data['name'];
                $menuStoreId = 0;
                $menuStore = $this->menuStoreModel->findOneBy(array('name' => $name));
                if($menuStore){
                    $menuStore->setName($data['name']);
                    $menuStore->setUnit($data['unit']);
                    $menuStore->setDes($data['des']);
                    $menuStore->setOutOfStock($data['OutOfStock']);
                    $menuStore->setCost(0);
                    $menuStore->setSupplier(0);
                    $menuStore->setSupplyItem($data['supplyType']);
                    $menuStore->setIsdelete(0);
                    $this->menuStoreModel->edit($menuStore);
                    $menuStoreId = $menuStore->getId();
                }
                else{
                //else add
                    $menuStore = new MenuStore();
                    $menuStore->setName($data['name']);
                    $menuStore->setUnit($data['unit']);
                    $menuStore->setDes($data['des']);
                    $menuStore->setOutOfStock($data['OutOfStock']);
                    $menuStore->setCost(0);
                    $menuStore->setSupplier(0);
                    $menuStore->setSupplyItem($data['supplyType']);
                    $menuStore->setIsdelete(0);
                    $menuStore = $this->menuStoreModel->insert($menuStore);
                    $menuStoreId = $menuStore->getId();
                }
                //insert transaction
                //insert transaction in menuID
                $data['menuStoreId'] = $menuStoreId;
                $data['quantity'] = $data['quantity']*INSERT_STORE;
                $data['action'] = INSERT_STORE_ACRION;
                $data['unit'] = $data['unit'];
                $data['note'] = $this->translator->translate('Import item into store');
                TransactionUtility::insertTransaction($data,0,0);

                //transaction main store
                $data['menuStoreId'] = $data['mainMenuStoreId'];
                $data['quantity'] = $data['quantity']*ADD_ORDER;
                $data['action'] = ADD_ORDER_ACTION;
                $data['unit'] = $data['unit'];
                $data['note'] = $this->translator->translate('Insert sub store');
                TransactionUtility::insertTransaction($data,0,0,MAIN_STORE);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert Success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'menustore'));

            }

            //insert new user

            return new ViewModel(array('title' => $this->translator->translate('Add Inventory')));
        } else {
            $menuStore = $this->menuStoreModel->findOneBy(array('id' => $id));

            if ($request->isPost()) {

                $data = $this->params()->fromPost();
                $idFormPost = $data['id'];
                $menuStore = $this->menuStoreModel->findOneBy(array('id' => $idFormPost));
                $menuStore->setName($data['name']);
                $menuStore->setUnit($data['unit']);
                $menuStore->setDes($data['des']);
                $menuStore->setOutOfStock($data['OutOfStock']);
                $menuStore->setCost(0);
                $menuStore->setIsdelete(0);
                $menuStore->setSupplier(0);
                $menuStore->setSupplyItem($data['supplyType']);
                $this->menuStoreModel->edit($menuStore);

                $menuStoreId = $menuStore->getId();

                //insert transaction
                //insert transaction in menuID
                $data['menuStoreId'] = $menuStoreId;
                $data['quantity'] = $data['quantity']*INSERT_STORE;
                $data['action'] = INSERT_STORE_ACRION;
                $data['unit'] = $data['unit'];
                $data['note'] = $this->translator->translate('Import item into store');
                TransactionUtility::insertTransaction($data,$data['supplier'],$data['cost']);


                //transaction main store
                $data['menuStoreId'] = $data['mainMenuStoreId'];
                $data['quantity'] = $data['quantity']*ADD_ORDER;
                $data['action'] = ADD_ORDER_ACTION;
                $data['unit'] = $data['unit'];
                $data['note'] = $this->translator->translate('Insert sub store');
                TransactionUtility::insertTransaction($data,0,0,MAIN_STORE);


                //flash
                $this->flashMessenger()->addSuccessMessage("Update Success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'menustore'));
            }

            return new ViewModel(array(
                'data' => $menuStore,
                'title' => $this->translator->translate('Edit Inventory:')
            ));

        }

    }


    public function detailAction(){

        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        $filter = $this->params()->fromRoute('filter_action');

        $menuStore = array();
        if($id != ''){
            $menuStore = $this->menuStoreModel->findOneBy(array('id' => $id));
            $menuStore = $this->menuStoreModel->convertSingleToArray($menuStore);
        }

        //fileter transaction
        $transactios = $this->transactionModel->findBy(
            array('menuStoreId'=>$id,'store'=>SUB_STORE),
            array('id'=>'DESC')
        );
        if($filter != '') {
            $transactios = $this->transactionModel->findBy(
                array('menuStoreId' => $id, 'action' => $filter, 'store' => SUB_STORE),
                array('id' => 'DESC')
            );
        }


        $transactios = $this->transactionModel->convertToArray($transactios);

        //setup stote item detail
        $storeItemDetail =  array(
            'link' => 'admin/index',
            'data' =>$menuStore,
            'heading' => array(
                'id' => 'Id',
                'name' => $this->translator->translate('Name'),
                'quantityInput' => $this->translator->translate('Quantity input'),
                'quantityOutput' => $this->translator->translate('Quantity out put'),
                'quantityInStock' => $this->translator->translate('Quantity in stock'),
                'outOfStock' => $this->translator->translate('Out of stock config'),
                'unit' => $this->translator->translate('Unit'),
                'cost' => $this->translator->translate('Cost'),
                'supplier' => $this->translator->translate('Supplier'),
                'des' => $this->translator->translate('Description'),
//                'image' => 'Image',
            )
        );

        //set up manager transaction
        $dataTransactios =  array(
            'tableTitle'=> $this->translator->translate('Manage transaction'),
            'link' => 'admin/menustore',
            'data' =>$transactios,
            'heading' => array(
                'id' =>  'Id',
                'menuStoreId' => $this->translator->translate('Menu store id'),
                'action' => $this->translator->translate('Action'),
                'quantity' => $this->translator->translate('Quantity'),
                'unit' => $this->translator->translate('Unit'),
                'cost' => $this->translator->translate('Cost'),
                'date' => $this->translator->translate('Date'),
                'supplier' => $this->translator->translate('Supplier'),
                'note' => $this->translator->translate('Note'),
            ),
            'hideDetailButton' => 1,
            'hideDeleteButton' => 1,
            'hideEditButton' => 1,
        );
        return new ViewModel(array(
            'storeDetail'=>$storeItemDetail,
            'transactionTable' =>$dataTransactios,
            'title' => $this->translator->translate('Detail Store Item').': '.$storeItemDetail['data']['name'],
            'id' => $id
        ));
    }


    public function deleteAction(){
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $menu = $this->menuStoreModel->findOneBy(array('id'=>$id));
            $menu->setIsdelete(1);
            $this->menuStoreModel->edit($menu);
           // $this->menuStoreModel->delete(array('id'=>$id));
            echo 1;
        }
        die;
    }


    public function managerInOutAction(){
        $columns = array(

            array('title' =>'ID', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'menuStoreId','dt' => 1, 'search'=>true, 'type' => 'number','formatter'=>function($d,$row){
                //$storeInfo = Utility::getStoreInfo( $d);
                return $d;
            } ),
            array('title' =>'Quantity', 'db' => 'quantity','dt' => 2, 'search'=>true, 'type' => 'number'),
            array('title' =>'Unit', 'db' => 'unit','dt' => 3, 'search'=>false, 'type' => 'text' ),
            array('title' =>'Cost', 'db' => 'cost','dt' => 4, 'search'=>true, 'type' => 'number'),

            array('title' =>'Action', 'db' => 'action','dt' => 5 ,'search'=>false, 'type' => 'text' ),

            array('title' =>'Time', 'db' => 'date','dt' => 6, 'search'=>true, 'type' => 'number','formatter'=>function($d,$row){
                return $d;
            }),

            array('title' =>'Note', 'db' => 'note','dt' => 7, 'search'=>false, 'type' => 'text','formatter'=>function($d,$row){
                return $d;
            }),


        );


        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/menustore');
        $table->setTablePrefix('m');


        $table->setAjaxCall('/admin/menustore/managerinout');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->transactionModel);
        //end config table


        return new ViewModel(array(
            'table' => $table,
            'title' => $this->translator->translate('Manage Import/Export')));
    }

    public function unitcalcAction(){

        $number_before = $this->params()->fromPost('number_before');
        $unit_store = $this->params()->fromPost('unit_store');
        $unit_input = $this->params()->fromPost('unit_input');
        $response = false;
        if($number_before != '' && $unit_store != '' && $unit_store != '' ){
            if($unit_store == $unit_input){
                $response = $number_before;
            } else {
                $calc = UnitCalcUtility::unitCalc();
                if(isset($calc[$unit_input][$unit_store]))   {

                    $unitConvert = $calc[$unit_input][$unit_store];
                    $response =  $number_before*$unitConvert;

                }else{
                    $response = false;
                }

            }

        }
        echo $response;
        die;

    }


    public function addMultiAction(){
        if($this->getRequest()->isPost()){
            $datas = $this->params()->fromPost('item');
            $menuStoreId = 0;
            foreach($datas as $data){

                    if($data['quantity'] >0){
                    $name = $data['name'];
                    $menuStore = $this->menuStoreModel->findOneBy(array('name' => $name));

                    if($menuStore){
                        $menuStore->setName($data['name']);
                        $menuStore->setUnit($data['unit']);
                        $menuStore->setDes('');
                        $menuStore->setOutOfStock(0);
                        $menuStore->setCost(0);
                        $menuStore->setSupplier(0);
                        $menuStore->setSupplyItem($data['supplyType']);
                        $menuStore->setIsdelete(0);
                        $this->menuStoreModel->edit($menuStore);
                        $menuStoreId = $menuStore->getId();
                    }
                    else{
                        //else add
                        $menuStore = new MenuStore();
                        $menuStore->setName($data['name']);
                        $menuStore->setUnit($data['unit']);
                        $menuStore->setDes('');
                        $menuStore->setOutOfStock(0);
                        $menuStore->setCost(0);
                        $menuStore->setSupplier(0);
                        $menuStore->setSupplyItem($data['supplyType']);
                        $menuStore->setIsdelete(0);
                        $menuStore = $this->menuStoreModel->insert($menuStore);
                        $menuStoreId = $menuStore->getId();
                    }
                    //insert transaction
                    //insert transaction in menuID
                    $data['menuStoreId'] = $menuStoreId;
                    $data['quantity'] = $data['quantity']*INSERT_STORE;
                    $data['action'] = INSERT_STORE_ACRION;
                    $data['unit'] = $data['unit'];
                    $data['note'] = $this->translator->translate('Import item into store');
                    TransactionUtility::insertTransaction($data,0,0);


                    //transaction main store
                    $data['menuStoreId'] = $data['id'];
                    $data['quantity'] = $data['quantity']*ADD_ORDER;
                    $data['action'] = ADD_ORDER_ACTION;
                    $data['unit'] = $data['unit'];
                    $data['note'] = $this->translator->translate('Insert sub store');
                    TransactionUtility::insertTransaction($data,0,0,MAIN_STORE);
                }
            }

            //flash
            $this->flashMessenger()->addSuccessMessage("Insert Success");
            $this->redirect()->toRoute('admin/child',array('controller'=>'menustore'));
        }
        return new ViewModel(array('title' => $this->translator->translate('Add Inventory Multi')));
    }
}