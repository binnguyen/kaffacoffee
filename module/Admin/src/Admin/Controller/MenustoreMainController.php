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
use Admin\Entity\MenuStoreMain;
use Admin\Model\menuStoreModel;
use Admin\Model\transactionModel;
use Zend\Mvc\Controller\AbstractActionController;
use Velacolib\Utility\Utility;
use Velacolib\Utility\UnitCalcUtility;
use Velacolib\Utility\TransactionUtility;
use Admin\Model\menuStoreMainModel;
use Zend\View\Model\ViewModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class MenustoreMainController extends    AdminGlobalController
{

    protected $menuStoreModel;
    protected $menuSubStoreModel;
    protected $transactionModel;
    protected $translator;
    protected $config;


    public function init(){
        parent::init();

        $this->menuStoreModel = new menuStoreMainModel($this->doctrineService);
        $this->menuSubStoreModel = new menuStoreModel($this->doctrineService);
        $this->transactionModel = new transactionModel($this->doctrineService);

    }

    public function indexAction()
    {

        $columns = array(

            array('title' =>'Id', 'db' => 'id', 'dt' => 0,'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Cost', 'db' => 'cost','dt' => 2, 'search'=>false, 'type' => 'number'),
            array('title' =>'In', 'db' => 'id','dt' => 3, 'search'=>true, 'type' => 'number','formatter'=>
                function($d,$row){
                    $quantityInput =  TransactionUtility::checkStore($d,INSERT_STORE_ACRION,MAIN_STORE);
                    if(isset($quantityInput[0])){
                        $input = $quantityInput[0]['sum_store'];
                    }else{
                        $input = 0;
                    }
                    return $input;

                }
            ),
            array('title' =>'Out', 'db' => 'id','dt' => 4, 'search'=>true, 'type' => 'number','formatter'=>
                function($d,$row){

                    $quantityOut = TransactionUtility::checkStore($d,ADD_ORDER_ACTION,MAIN_STORE);
                    if(isset($quantityOut[0])){ $output = $quantityOut[0]['sum_store'];  }else{$output = 0;}
                    return $output;

                } ),
            array('title' =>'In Stock', 'db' => 'quantityInStock','dt' => 5 , 'search'=>false, 'type' => 'number'

            ),
            array('title' =>'Unit', 'db' => 'unit','dt' => 6 ,'search'=>false, 'type' => 'text',

//                'formatter'=>
//                    function($d,$row){
//                        $unitList = Utility::getUnitListForSelect();
//                        return $unitList[$d];
//                    }
            ),

            array('title' =>'Supplier', 'db' => 'supplier','dt' => 7, 'search'=>true, 'type' => 'text' ),

            array('title' =>'Action','db'=>'id','dt' => 8 , 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/menustoremain';
                    $actionTransactionUrl = '/admin/transaction';

                    return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/detail/'.$d.'"><i class="icon-info-sign"></i></a>
                         <a data-id="'.$d.'" id="'.$d.'" href="'.$actionTransactionUrl.'/inserttransactionmain/'.$d.'" class="btn-xs action action-detail btn btn-danger"><i class="icon-signin"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';

                }
            ),


        );


        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/menustoremain');
        $table->setTablePrefix('m');
//        $table->setExtendJoin(
//            array(
//                array(" Admin\\Entity\\Transaction", "t", "WITH", " t.menuStoreId = m.id "),
//                array(" Admin\\Entity\\Supplier", "s", "WITH", " s.id = m.supplier "),
//            )
//        );

        $table->setExtendSQl(array(
            array('AND','m.isdelete','=','0'),
        ));
        $table->setAjaxCall('/admin/menustoremain/index');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->menuStoreModel);
        //end config table


        return new ViewModel(array(
                'table' => $table,
                'title' => $this->translator->translate('Manage Warehouse'))
        );


    }

    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if ($id == '') {
            if ($request->isPost()) {

                $data = $this->params()->fromPost();
                $isImportMenuStore = isset($data['import-menustore'])?$data['import-menustore']:'';
                //check name
                // if exists update
                $rawMaterial = Utility::getRawMaterialInfo($data['supplyType']);


                $menuStoreId = 0;
                $menuStore = $this->menuStoreModel->findOneBy(array('name' => $rawMaterial->getValue()));
                //$this->menuStoreModel->begin();
                if($menuStore){
                    $menuStore->setName($rawMaterial->getValue());
                    $menuStore->setUnit($data['unit']);
                    $menuStore->setDes($data['des']);
                    $menuStore->setOutOfStock($data['OutOfStock']);
                    $menuStore->setCost($data['cost']);
                    $menuStore->setSupplier($data['supplier']);
                    $menuStore->setSupplyItem($data['supplyType']);
                    $menuStore->setIsdelete(0);
                    $this->menuStoreModel->edit($menuStore);
                    $menuStoreId = $menuStore->getId();
                    //$this->menuStoreModel->commit();
                    if($isImportMenuStore == 1){
                        $this->insertTransaction($menuStoreId,$data);

                    }
                }
                else{
                    //else add

                    $rawMaterial = Utility::getRawMaterialInfo($data['supplyType']);
                    $menuStore = new MenuStoreMain();
                    $menuStore->setName($rawMaterial->getValue());
                    $menuStore->setUnit($data['unit']);
                    $menuStore->setDes($data['des']);
                    $menuStore->setOutOfStock($data['OutOfStock']);
                    $menuStore->setCost($data['cost']);
                    $menuStore->setSupplier($data['supplier']);
                    $menuStore->setSupplyItem($data['supplyType']);
                    $menuStore->setIsdelete(0);
                    $menuStore = $this->menuStoreModel->insert($menuStore);
                    $menuStoreId = $menuStore->getId();
                    //$this->menuStoreModel->commit();
                    if($isImportMenuStore == 1){
                        $this->insertTransaction($menuStoreId,$data);

                    }

                }
                //insert transaction
                //insert transaction in menuID
                $data['menuStoreId'] = $menuStoreId;
                $data['quantity'] = $data['quantity']*INSERT_STORE;
                $data['action'] = INSERT_STORE_ACRION;
                $data['unit'] = $data['unit'];
                $data['note'] = $this->translator->translate('Import item into store');
                TransactionUtility::insertTransaction($data,$data['supplier'],$data['cost'],MAIN_STORE);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert Success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'menustoremain'));

            }

            //insert new user

            return new ViewModel(array('title' => $this->translator->translate('Add Warehouse')));
        }
        else {
            $menuStore = $this->menuStoreModel->findOneBy(array('id' => $id));

            if ($request->isPost()) {

                $data = $this->params()->fromPost();
                $rawMaterial = Utility::getRawMaterialInfo($data['supplyType']);
                $idFormPost = $data['id'];
                $menuStore = $this->menuStoreModel->findOneBy(array('id' => $idFormPost));
                $menuStore->setName($rawMaterial->getValue());
                $menuStore->setUnit($data['unit']);
                $menuStore->setDes($data['des']);
                $menuStore->setOutOfStock($data['OutOfStock']);
                $menuStore->setCost($data['cost']);
                $menuStore->setIsdelete(0);
                $menuStore->setSupplier($data['supplier']);
                $menuStore->setSupplyItem($data['supplyType']);
                $this->menuStoreModel->edit($menuStore);

                $menuStoreId = $menuStore->getId();

                //insert transaction
                //insert transaction in menuID
                $data['menuStoreId'] = $menuStoreId;
                $data['quantity'] = $data['quantity']*INSERT_STORE;
                $data['action'] = INSERT_STORE_ACRION;
                $data['unit'] = $data['unit'];
                $data['note'] = $this->translator->translate('Import Item Into Store');
                TransactionUtility::insertTransaction($data,$data['supplier'],$data['cost'],MAIN_STORE);

                $this->flashMessenger()->addSuccessMessage("Update Success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'menustoremain'));
            }

            return new ViewModel(array(
                'data' => $menuStore,
                'title' => $this->translator->translate('Edit Warehouse:')
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
            array('menuStoreId'=>$id,'store'=>MAIN_STORE),
            array('id'=>'DESC')
        );
        if($filter != '') {
            $transactios = $this->transactionModel->findBy(
                array('menuStoreId' => $id, 'action' => $filter, 'store' => MAIN_STORE),
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

        $id = $this->params()->fromRoute('id');
        $menu = $this->menuStoreModel->findOneBy(array('id'=>$id));
        $menu->setIsdelete(1);
        $this->menuStoreModel->edit($menu);
        // $this->menuStoreModel->delete(array('id'=>$id));
        $this->flashMessenger()->addSuccessMessage('Hide item success');
        $this->redirect()->toRoute('admin/child',array(
            'controller'=>'menustoremain',
            'action'=>'index'
        ));
    }

    public function managerInOutAction(){
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0,'select'=>'id','prefix'=>'o', 'search'=>false, 'type' => 'number' ),
            array('title' =>'Store Name', 'db' => 'menuStoreId','dt' => 1,'select'=>'menuStoreId','prefix'=>'o', 'search'=>true, 'type' => 'text','formatter'=>function($d,$row){
                $storeInfo = Utility::getMainStoreInfo( $d);
                return $storeInfo->getName();
            } ),
            array('title' =>'Action', 'db' => 'action','dt' => 2,'select'=>'action','prefix'=>'o', 'search'=>true, 'type' => 'text' ),
            array('title' =>'Quantity', 'db' => 'quantity','dt' => 3,'select'=>'quantity','prefix'=>'o', 'search'=>true, 'type' => 'text' ),
            array('title' =>'Cost', 'db' => 'cost','dt' => 4,'select'=>'cost','prefix'=>'o', 'search'=>true, 'type' => 'text' ),
            array('title' =>'Note', 'db' => 'note','dt' => 5,'select'=>'note','prefix'=>'o', 'search'=>true, 'type' => 'text','formatter'=>
                function($d,$row){
                    $note = TransactionUtility::getStoreItemInOrder($d);
                    if($note == ''){
                        $note = $d;
                    }
                    return $note;
                }),

            array('title' =>'Action', 'db' => 'orderId','dt' => 6, 'select'=>'id','prefix'=>'o', 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {

                    $actionUrl = '/admin/menustoremain';
                    return '
                        <a class="btn-xs action action-detail btn btn-info btn-default" href="'.$actionUrl.'/detail/'.$d.'"><i class="icon-info-sign"></i></a>
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';

                }
            )

        );
        /////end column for table
        $table = new AjaxTable(array(), array(), 'admin/menustoremain/managerinout');
        $table->setTableColumns($columns);
        $table->setTablePrefix('o');


        $table->setAjaxCall('/admin/menustoremain/managerinout');
        $table->setActionDeleteAll('deleteall');


        $this->tableAjaxRequest($table,$columns,$this->transactionModel);


        return new ViewModel(
            array('table' => $table,
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
                $unitInputInfo = Utility::getUnit($unit_input);
                $unitStoreInfo = Utility::getUnit($unit_store);
                if(isset($calc[$unitInputInfo->getShortName()][$unitStoreInfo->getShortName()]))   {
                    $unitConvert = $calc[$unitInputInfo->getShortName()][$unitStoreInfo->getShortName()];
                    $response =  $number_before*(float)$unitConvert;
                }else{
                    $response = false;
                }
            }
        }
        echo $response;
        die;

    }

    protected function insertTransaction($menuStoreId,$data){
        $subStoreId = $this->insertSubStore($data);
        //insert transaction
        //insert transaction in menuID
        $data['menuStoreId'] = $subStoreId;
        $data['quantity'] = $data['quantity']*INSERT_STORE;
        $data['action'] = INSERT_STORE_ACRION;
        $data['unit'] = $data['unit'];
        $data['note'] = $this->translator->translate('Import item into store');
        TransactionUtility::insertTransaction($data,0,0);

        //transaction main store
        $data['menuStoreId'] = $menuStoreId;
        $data['quantity'] = $data['quantity']*ADD_ORDER;
        $data['action'] = ADD_ORDER_ACTION;
        $data['unit'] = $data['unit'];
        $data['note'] = $this->translator->translate('Insert sub store');
        TransactionUtility::insertTransaction($data,0,0,MAIN_STORE);
    }
    protected function insertSubStore($data){


        $rawMaterial = Utility::getRawMaterialInfo($data['supplyType']);
        $menuSubStore = $this->menuSubStoreModel->findOneBy(array('name'=>$rawMaterial->getValue()));
        if($menuSubStore){
            $menuSubStore->setName($rawMaterial->getValue());
            $menuSubStore->setUnit($data['unit']);
            $menuSubStore->setOutOfStock($data['OutOfStock']);
            $menuSubStore->setCost(0);
            $menuSubStore->setSupplier(0);
            $menuSubStore->setDes('');
            $menuSubStore->setSupplyItem($data['supplyType']);
            $menuSubStore->setIsdelete(0);

            $menuSubStore = $this->menuSubStoreModel->insert($menuSubStore);
            $menuSubStoreId = $menuSubStore->getId();
            return $menuSubStoreId;
        }
        else{
            $menuSubStore = new MenuStore();
            $menuSubStore->setName($rawMaterial->getValue());
            $menuSubStore->setUnit($data['unit']);
            $menuSubStore->setOutOfStock($data['OutOfStock']);
            $menuSubStore->setCost(0);
            $menuSubStore->setSupplier(0);
            $menuSubStore->setDes('');
            $menuSubStore->setSupplyItem($data['supplyType']);
            $menuSubStore->setIsdelete(0);

            $menuSubStore = $this->menuSubStoreModel->insert($menuSubStore);
            $menuSubStoreId = $menuSubStore->getId();
            return $menuSubStoreId;
        }
        return -1;
    }
}