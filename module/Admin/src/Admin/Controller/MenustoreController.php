<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/6/14
 * Time: 1:56 PM
 */

namespace Admin\Controller;

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

class MenustoreController extends BaseController
{

    protected $menuStoreModel;
    protected $transactionModel;
    protected $translator;
    protected $config;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->menuStoreModel = new menuStoreModel($doctrine);
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

    public function ajaxListAction()
    {

        $fields = array(
            'id',
            'name',
            'unit',
            'des',
            'cost',
            'supplier',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = ' c.isdelete = 0';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }

        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\MenuStore c";



        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql .$customQuery  . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\MenuStore c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        // map data
        $ret = array_map(function($item) {
            $quantityInput = TransactionUtility::checkStore($item->getId(),INSERT_STORE_ACRION);
            if(isset($quantityInput[0])){
                $input = $quantityInput[0]['sum_store'];
            }else{ $input = 0; }


            $quantityOut = TransactionUtility::checkStore($item->getId(),ADD_ORDER_ACTION);
            if(isset($quantityOut[0])){ $output = $quantityOut[0]['sum_store'];  }else{$output = 0;}

            $linkEdit =   '/admin/menustore/add/'.$item->getId() ;
            $linkDelete =  '/admin/menustore/delete/'.$item->getId() ;
            $linkDetail =   '/admin/menustore/detail/'.$item->getId() ;
            $linkEditTransaction =   '/admin/transaction/inserttransaction/'.$item->getId() ;

            return array(
                'id' => $item->getId(),
                'name' => $item->getName() ,
                'unit' => $item->getUnit(),
                'des' =>$item->getDes(),
                'quantity_in_stock'=>  number_format(TransactionUtility::getMenuItemQuantityInStore( $item->getId())),
                'cost'=>$item->getCost(),
                'supplier'=>$item->getSupplier(),
                'supply_item'=>$item->getSupplyItem(),
                'quantityInput'=>$input,
                'quantityOut'=>$output,
                'inMenu'=>Utility::getMenuInMenuStore($item->getId())  ,
                'action'=> '
                <a target="_blank" href="'.$linkDetail.'" class="btn btn-info"><i class="icon-edit-sign"></i>
                </a>
                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a  id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>
                <a href="'.$linkEditTransaction.'" class="btn btn-danger"><i class="icon-edit"></i></a>
                '
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }

    public function indexAction()
    {
        return new ViewModel(array(
            'title' => $this->translator->translate('Menu store')
        ));
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
                'title' => $this->translator->translate('Edit menu store:')
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
        $filter = $this->params()->fromRoute('filter_action');
        $fromDate = $this->params()->fromRoute('fromdate');
        $toDate = $this->params()->fromRoute('todate');
        $dataStore = $this->transactionModel->findBy(array('store'=>SUB_STORE));
        if($filter != ''){
            $dataStore = $this->transactionModel->findBy(array('action'=>$filter,'store'=>SUB_STORE));
        }
        $dataRow = $this->transactionModel->convertToArray($dataStore);
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
                'date' => $this->translator->translate('Date'),
                'note' => $this->translator->translate('Note'),
            ),
            'hideDetailButton' => 1,
            'hideDeleteButton' => 1,
            'hideEditButton' => 1,
        );

        return new ViewModel(
            array('data' => $data,
                'title' => $this->translator->translate('Manager in out')));
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
        return new ViewModel(array('title' => $this->translator->translate('Add new menu store multi')));
    }
}