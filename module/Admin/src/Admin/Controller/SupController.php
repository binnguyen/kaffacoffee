<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Supplier;
use Admin\Entity\SupplierFor;
use Admin\Entity\SupplierItem;
use Admin\Entity\Table;

use Admin\Form\eventForm;
use Admin\Form\supplierForm;
use Admin\Model\supplierModel;
use Admin\Model\supplyForModel;
use Admin\Model\supplyItemModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class SupController extends BaseController
{
    protected   $modelSupplier;
    protected   $modelSupplyFor;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelSupplier = new supplierModel($doctrine);
        $this->modelSupplyFor = new supplyForModel($doctrine);


        $this->translator =  Utility::translate();

        //check login
        $user = Utility::checkLogin($this);
        if(! is_object($user) && $user == 0){
            $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }else{
            $isPermission = Utility::checkRole($user->userType,ROLE_ADMIN);
            if( $isPermission == false)
                $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }

        return parent::onDispatch($e);
    }

    public function ajaxListAction()
    {

        $fields = array(
            'id',
            'phone',
            'mobile',
            'addr',
            'contactName',
            'email',
            'suplierFor',
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
        $dql = "SELECT c FROM Admin\Entity\Supplier c ";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql .$customQuery. $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }

        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Supplier c WHERE c.isdelete =0";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        $ret = array_map(function($item) {

            $linkEdit =   '/admin/supplier/add/'.$item->getId() ;
            $linkDelete =  '/admin/supplier/delete/'.$item->getId() ;
            $linkDetail =   '/admin/supplier/detail/'.$item->getId() ;

            return array(
                'id' => $item->getId(),
                'phone' => $item->getPhone(),
                'mobile'=>$item->getMobile(),
                'addr'=>$item->getAddr(),
                'contact_name'=>$item->getContactName(),
                'email'=>$item->getEmail(),
                'suplier_for'=>$item->getSuplierFor(),
                'action' => '

                 <a href="'.$linkEdit.'" class="btn btn-primary"><i class="icon-edit-sign"></i></a>
                 <a  id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }

    public function indexAction()
    {
        return new ViewModel(array(
            'title'=>$this->translator->translate('Supplier'
            )));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $event = new Supplier();
            $configForm = new supplierForm();
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $event->setCompanyName($data['company']);
                $event->setContactName($data['name']);
                $event->setAddr($data['addr']);
                $event->setPhone($data['phone']);
                $event->setMobile($data['mobile']);
                $event->setEmail($data['email']);
                $event->setSuplierFor(0);
                $event->setIsdelete(0);
                $inserted= $this->modelSupplier->insert($event);

                $dataSuplyFor = $data['supply_for'];
                foreach($dataSuplyFor as $item){
                    $this->insertSupplyItem($item,$inserted->getId());
                }

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'supplier'));
            }

            return new ViewModel(array(
                'data' =>$event,
                'title' => $this->translator->translate('Add new supplier'),
                'form' => $configForm
            ));
        }
        else{
            $event = $this->modelSupplier->findOneBy(array('id'=>$id));
            $configForm = new supplierForm();
            $configForm->setAttribute('action', '/admin/supplier/add/'.$id);
            $arraySupplyfor = Utility::getSupplyItemOfSupplier($id);



            $configForm->get('id')->setValue($event->getId());
            $configForm->get('company')->setValue($event->getCompanyName());
            $configForm->get('name')->setValue($event->getContactName());
            $configForm->get('phone')->setValue($event->getPhone());
            $configForm->get('mobile')->setValue($event->getMobile());
            $configForm->get('email')->setValue($event->getEmail());
            $configForm->get('addr')->setValue($event->getAddr());
            $configForm->get('supply_for')->setValue($this->parseToArraySelect($arraySupplyfor));



            if($request->isPost()){
                $data = $this->params()->fromPost();
                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelSupplier->findOneBy(array('id'=>$idFormPost));
                $event->setCompanyName($data['company']);
                $event->setContactName($data['name']);
                $event->setAddr($data['addr']);
                $event->setPhone($data['phone']);
                $event->setMobile($data['mobile']);
                $event->setEmail($data['email']);
                $event->setSuplierFor(0);
                $event->setIsdelete(0);
                $this->modelSupplier->edit($event);

                //update supply item
                $this->modelSupplyFor->deleteAll(array('suppilerId'=>$idFormPost));
                $dataSuplyFor = $data['supply_for'];
                foreach($dataSuplyFor as $item){
                    $this->insertSupplyItem($item,$idFormPost);
                }
                $arraySupplyfor = Utility::getSupplyItemOfSupplier($id);
                //update form
                $configForm->get('company')->setValue($event->getCompanyName());
                $configForm->get('name')->setValue($event->getContactName());
                $configForm->get('phone')->setValue($event->getPhone());
                $configForm->get('mobile')->setValue($event->getMobile());
                $configForm->get('email')->setValue($event->getEmail());
                $configForm->get('addr')->setValue($event->getAddr());
                $configForm->get('supply_for')->setValue($this->parseToArraySelect($arraySupplyfor));

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'supplier'));
            }
            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit event: '.$event->getCompanyName(),
                'form' => $configForm
            ));
        }
    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $event = $this->modelSupplier->findOneBy(array('id'=>$id));
            $event->setIsdelete(1);
            $this->modelSupplier->edit($event);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {


    }
    private function insertSupplyItem($suplyForItemID,$supplierID){
        $suplyForItem = new SupplierFor();
        $suplyForItem->setSuppilerId($supplierID);
        $suplyForItem->setSupplierItem($suplyForItemID);
        $this->modelSupplyFor->insert($suplyForItem);
    }


    private function parseToArraySelect($data){
        $array = array();
        foreach($data as $item){
            $array[$item['id']] = $item['id'];
        }
        return $array;
    }

    public function getsuplierAction(){

        $suplierItemId = $this->params()->fromPost('suplier_item_id');
         $supplier  = array();
        $suplierFor = $this->modelSupplyFor->findBy(array('supplierItem'=>$suplierItemId));
        $response = array();
         if(! empty($suplierFor)){
             foreach($suplierFor as $suplierForItem){
                 $suppli = Utility::getSupplierInfo($suplierForItem->getSuppilerId());
                 if($suppli->getIsdelete() == 0){
                    $supplier[$suppli->getId()] = $suppli->getCompanyName() ;
                 }
             }
             $response['status'] = true;
             $response['result'] =   ($supplier);

         } else{
             $response['status'] = false;
         }
        echo json_encode($response);
        die;



    }
}