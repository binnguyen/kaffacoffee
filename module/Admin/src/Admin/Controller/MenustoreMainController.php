<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/6/14
 * Time: 1:56 PM
 */

namespace Admin\Controller;

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


class MenustoreMainController extends    BaseController
{

    protected $menuStoreModel;
    protected $menuSubStoreModel;
    protected $transactionModel;
    protected $translator;
    protected $config;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->menuStoreModel = new menuStoreMainModel($doctrine);
        $this->menuSubStoreModel = new menuStoreModel($doctrine);
        $this->transactionModel = new transactionModel($doctrine);
        $this->config = Utility::getConfig();
        $this->translator = Utility::translate();
        //check login
        $user = Utility::checkLogin();
        if (!is_object($user) && $user == 0) {
            $this->redirect()->toRoute('admin/child', array('controller' => 'login'));
        } else {
            $isPermission = Utility::checkRole($user->userType, ROLE_ADMIN);
            if ($isPermission == false)
                $this->redirect()->toRoute('admin/child', array('controller' => 'login'));
        }


        //end check login

        return parent::onDispatch($e);
    }

    public function ajaxListAction(){

        $fields = array(
            'id',
            'name',
            'unit',
            'des',
            'cost',
            'supplier',
            'outOfStock',
            'supplyItem',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();

        $customWhere  = ' c.isdelete = 0 ';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);

        // WHERE conditions
        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }


        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\MenuStoreMain c";



        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\MenuStoreMain c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();




        // map data
        $ret = array_map(function($item) {
            $quantityInput = TransactionUtility::checkStore($item->getId(),INSERT_STORE_ACRION,MAIN_STORE);
            if(isset($quantityInput[0])){
                $input = $quantityInput[0]['sum_store'];
            }else{ $input = 0; }
            $quantityOut = TransactionUtility::checkStore($item->getId(),ADD_ORDER_ACTION,MAIN_STORE);
            if(isset($quantityOut[0])){ $output = $quantityOut[0]['sum_store'];  }else{$output = 0;}
            $supplier = Utility::getSupplierInfo($item->getSupplier());

            // create link
           $linkEdit =   '/admin/menustoremain/add/'.$item->getId() ;
           $linkDelete =  '/admin/menustoremain/delete/'.$item->getId() ;
           $linkDetail =   '/admin/menustoremain/detail/'.$item->getId() ;
           $linkEditTransaction =   '/admin/transaction/inserttransactionmain/'.$item->getId() ;



            return array(
                'id' => $item->getId(),
                'name' => $item->getName() ,
                'unit' => $item->getUnit(),
                'des' =>$item->getDes(),
                'quantity_in_stock'=>  TransactionUtility::getMenuItemQuantityInStore( $item->getId(),MAIN_STORE),
                'cost'=>$item->getCost(),
                'supplier'=>$supplier->getCompanyName(),
                'quantityInput'=>$input,
                'quantityOut'=>$output,
                'supplyType'=> $item->getSupplyItem(),
                'action'=> '<a target="_blank" href="'.$linkDetail.'" class="btn btn-info"><i class="icon-info-sign"></i></a><a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a href="'.$linkDelete.'" class="btn btn-danger"><i class="icon-trash"></i></a>
                <a href="'.$linkEditTransaction.'" class="btn btn-danger"><i class="icon-edit"></i></a>
                '
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }

    public function indexAction()
    {

        return new ViewModel(array(
            'title' => $this->translator->translate('Menu store')));
    }

    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if ($id == '') {
            if ($request->isPost()) {

                $data = $this->params()->fromPost();
                $isImportMenuStore = $data['import-menustore'];
                //check name
                // if exists update
                $name = $data['name'];
                $menuStoreId = 0;
                $menuStore = $this->menuStoreModel->findOneBy(array('name' => $name));
                //$this->menuStoreModel->begin();
                if($menuStore){
                    $menuStore->setName($data['name']);
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
                    $menuStore = new MenuStoreMain();
                    $menuStore->setName($data['name']);
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

            return new ViewModel(array('title' => $this->translator->translate('Add new menu store')));
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
                $data['note'] = $this->translator->translate('Import item into store');
                TransactionUtility::insertTransaction($data,$data['supplier'],$data['cost'],MAIN_STORE);

                $this->flashMessenger()->addSuccessMessage("Update Success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'menustoremain'));
            }

            return new ViewModel(array(
                'data' => $menuStore,
                'title' => $this->translator->translate('Edit menu store:')
            ));

        }

    }


    public function detailAction(){

        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        return new ViewModel(array(
            'title' => $this->translator->translate('Detail Store Item'),
            'id' => $id
        ));
    }

    public function detailAjaxAction(){

        $menuStoreId = $this->params()->fromRoute('id');

        $fields = array(
            'id',
            'menuStoreId',
            'action',
            'quantity',
            'unit',
            'date',
            'note',
            'cost',
            'supplier',
            'store',
            'orderId',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = ' c.menuStoreId = '.$menuStoreId;
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);

        // WHERE conditions
        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }

        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\Transaction c  ";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Transaction c WHERE c.menuStoreId = ".$menuStoreId;
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        // map data
        $ret = array_map(function($item) {



            $storeInfo = Utility::getMainStoreInfo( $item->getMenuStoreId());
           // $note = TransactionUtility::getStoreItemInOrder($item->getNote());
            $supplier  = Utility::getSupplierInfo($item->getSupplier());
            return array(
                'id' => $item->getId(),
                'menuStoreId' => $storeInfo->getName() ,
                'action' => $item->getAction(),
                'quantity' =>$item->getQuantity(),
                'unit'=> $item->getUnit(),
                'date'=>date('d-m-Y',$item->getDate()),
                'cost'=> $item->getCost(),
                'note'=> $item->getNote(),
                'supplier'=> $supplier->getCompanyName(),

            );
        }, $results);



        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);
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
        $filter = $this->params()->fromRoute('filter_action');
        $fromDate = $this->params()->fromRoute('fromdate');
        $toDate = $this->params()->fromRoute('todate');
        $dataStore = $this->transactionModel->findBy(array('store'=>MAIN_STORE));
        if($filter != ''){
            $dataStore = $this->transactionModel->findBy(array('action'=>$filter,'store'=>MAIN_STORE));
        }
        $dataRow = $this->transactionModel->convertToArray($dataStore,MAIN_STORE);

        $data = array(
            'tableTitle' => $this->translator->translate('Manager In out'),
            'link' => 'admin/menustore',
            'data' => $dataRow,
            'heading' => array(
                'id' => 'Id',
                'menuStoreId' => $this->translator->translate('Name'),
                'quantity' => $this->translator->translate('Quantity'),
                'unit' => $this->translator->translate('Unit'),
                'cost' => $this->translator->translate('Cost'),
                'unit' => $this->translator->translate('Unit'),
                'action' => $this->translator->translate('Action'),
                'supplier' => $this->translator->translate('Supplier'),
                'date' => $this->translator->translate('Date'),
                'note' => $this->translator->translate('Note'),
            ),
            'hideDetailButton' => 1,
            'hideDeleteButton' => 1,
            'hideEditButton' => 1,
        );

        return new ViewModel(
            array('data' => $data,
                'title' => $this->translator->translate('Manager In out')));
    }

    public function inOutAjaxAction(){

        $fields = array(
            'id',
            'menuStoreId',
            'action',
            'quantity',
            'unit',
            'date',
            'note',
            'cost',
            'supplier',
            'store',
            'orderId',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = '';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }
        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\Transaction c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Transaction c ";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        // map data
        $ret = array_map(function($item) {

            $storeInfo = Utility::getMainStoreInfo( $item->getMenuStoreId());

            // create link
            $linkEdit =   '/admin/menustoremain/add/'.$item->getId() ;
            $linkDelete =  '/admin/menustoremain/delete/'.$item->getId() ;
            $linkDetail =   '/admin/menustoremain/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'menuStoreId' =>$storeInfo->getName() ,
                'action' => $item->getAction(),
                'quantity' =>$item->getQuantity(),
                'unit'=>  $item->getUnit(),
                'date'=>$item->getDate(),
                'note'=>$item->getNote(),
                'cost'=>$item->getCost(),
                'supplier'=>$item->getSupplier(),
                'store'=> $item->getStore(),
                'orderId'=> $item->getOrderId(),
                'actions'=> '
                <a href="'.$linkDetail.'" class="btn btn-info"><i class="icon-info-sign"></i></a>
                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a href="'.$linkDelete.'" class="btn btn-danger"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

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

        $name = $data['name'];
        $menuSubStore = $this->menuSubStoreModel->findOneBy(array('name'=>$name));
        if($menuSubStore){
            $menuSubStore->setName($data['name']);
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
            $menuSubStore->setName($data['name']);
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