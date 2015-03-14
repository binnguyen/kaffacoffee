<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Categories;
use Admin\Entity\Table;
use Admin\Entity\Transaction;
use Admin\Model\menuStoreMainModel;
use Admin\Model\menuStoreModel;
use Admin\Model\transactionModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;



class TransactionController extends BaseController
{
    protected  $modelTransaction;
    protected  $modelMenuStore;
    protected  $modelMenuStoreMain;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $CategoriesTable = $this->sm->get($service_locator_str);
        $this->modelTransaction = new transactionModel($CategoriesTable);
        $this->modelMenuStore = new menuStoreModel($CategoriesTable);
        $this->modelMenuStoreMain = new menuStoreMainModel($CategoriesTable);
        $this->translator = Utility::translate();
        //check login
        $user = Utility::checkLogin($this);
        if(! is_object($user) && $user == 0){
            $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }else{
            $isPermission = Utility::checkRole($user->userType,ROLE_ADMIN);
            if( $isPermission == false)
                $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }
        //end check login

        return parent::onDispatch($e);
    }
    public function indexAction()
    {

        return new ViewModel(array('title'=> $this->translator->translate('Transaction')));
    }
    public function ajaxListAction()
    {

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

        // WHERE conditions
        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search);

        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\Transaction c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $dqlWhere . $dqlOrder);
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
            $linkEdit =   '/admin/transaction/add/'.$item->getId() ;
            $linkDelete =  '/admin/transaction/delete/'.$item->getId() ;
            $linkDetail =   '/admin/transaction/detail/'.$item->getId() ;
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
                'actions'=> '<a href="'.$linkDetail.'" class="btn btn-info"><i class="icon-edit-sign"></i></a><a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a><a href="'.$linkDelete.'" class="btn btn-danger"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            if($request->isPost()) {
                $cat = new Categories();
                $cat->setName($this->params()->fromPost('name'));
                $cat->setIsdelete(0);
                $catInserted = $this->modelCategories->insert($cat);
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            return new ViewModel(array('title'=> $this->translator->translate('Add new category')));
        }
        else{

            $cat = $this->modelCategories->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelCategories->findOneBy(array('id'=>$idFormPost));
                $cat->setName($this->params()->fromPost('name'));
                $cat->setIsdelete(0);
                $this->modelCategories->edit($cat);
            }
            return new ViewModel(array(
                'data' =>$cat,
                'title' => $this->translator->translate('Edit category').': '.$cat->getName()
            ));
        }
    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $menu = $this->modelCategories->findOneBy(array('id'=>$id));
            $menu->setIsdelete(1);
            $this->modelCategories->edit($menu);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {
        //get user by id
        $id = $this->params()->fromRoute('id');
        $user = $this->model->findOneBy(array('id'=>$id));
        $user->setFullName('tri 1234');
        $this->model->edit($user);
        //update user

    }
    public function insertTransactionAction(){
        $menuStoreId = $this->params()->fromRoute('id');
        $menuStore = $this->modelMenuStore->findOneBy(array('id'=>$menuStoreId));
        $request = $this->getRequest();
        if($request->isPost()){
            $data = $this->params()->fromPost();

            if($data['quantity'] != '' && $data['mainMenuStoreId'] != '' && $data['toDate'] !='' ){



                $transaction = new Transaction();
                $transaction->setMenuStoreId($data['mainMenuStoreId']);
                $transaction->setAction($data['action']);
                $transaction->setQuantity($data['quantity']);
                $transaction->setUnit($data['unit']);
                $transaction->setDate(strtotime($data['toDate']));
                $transaction->setNote($data['des']);
                $transaction->setCost($data['cost']);
                $transaction->setSupplier(0);
                $transaction->setOrderId(0);
                $transaction->setStore(SUB_STORE);
                $inserted = $this->modelTransaction->insert($transaction);

                if($inserted){

                    $this->flashMessenger()->addSuccessMessage("Insert success");
                    return $this->redirect()->toRoute('admin/child',array(
                        'controller'=>'menustore',
                        'action'=> 'detail',
                        'id' => $inserted->getMenuStoreId(),
                        'filter_action' => $inserted->getAction()
                        )
                    );
                }
            }


        }
        return new ViewModel(array('menuStoreId'=>$menuStoreId,'menuStore'=>$menuStore));
    }
    public function insertTransactionMainAction(){
        $menuStoreId = $this->params()->fromRoute('id');
        $menuStoreMain = $this->modelMenuStoreMain->findOneBy(array('id'=>$menuStoreId));
        $request = $this->getRequest();
        $request = $this->getRequest();
        if($request->isPost()){
            $data = $this->params()->fromPost();
            if($data['quantity'] != '' && $data['mainMenuStoreId'] != '' && $data['toDate'] !='' ){
                $transaction = new Transaction();
                $transaction->setMenuStoreId($data['mainMenuStoreId']);
                $transaction->setAction($data['action']);
                $transaction->setQuantity($data['quantity']);
                $transaction->setUnit($data['unit']);
                $transaction->setDate(strtotime($data['toDate']));
                $transaction->setNote($data['des']);
                $transaction->setCost($data['cost']);
                $transaction->setSupplier(0);
                $transaction->setOrderId(0);
                $transaction->setStore(MAIN_STORE);
                $inserted = $this->modelTransaction->insert($transaction);
                if($inserted){
                    $this->flashMessenger()->addSuccessMessage("Insert success");
                    $this->redirect()->toRoute('admin/child',array(
                            'controller'=>'menustoremain',
                            'action'=> 'detail',
                            'id' => $inserted->getMenuStoreId(),
                            'filter_action' => $inserted->getAction()
                        )
                    );
                }
            }

        }
        return new ViewModel(array('menuStoreId'=>$menuStoreId,'menuStore'=>$menuStoreMain));
    }
}