<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/28/14
 * Time: 10:04 AM
 */

namespace Admin\Controller;


use Admin\Model\supplyForModel;
use Zend\Mvc\Controller\AbstractActionController;

use Admin\Entity\SupplierItem;
use Admin\Entity\Table;
use Admin\Form\supplieritemForm;


use Admin\Model\supplyItemModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class SupplieritemController  extends BaseController {

    protected   $modelSubItem;
    protected   $modelSubItemFor;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelSubItem = new supplyItemModel($doctrine);
        $this->modelSubItemFor = new supplyForModel($doctrine);


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


    public function ajaxListAction(){

        $fields = array(
            'id',
            'value',
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
        $dql = "SELECT c FROM Admin\Entity\SupplierItem c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\SupplierItem c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();




        // map data
        $ret = array_map(function($item) {
                    // create link
            $linkEdit =   '/admin/supplieritem/add/'.$item->getId() ;
            $linkDelete =  '/admin/supplieritem/delete/'.$item->getId() ;
            $linkDetail =   '/admin/supplieritem/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'value' => $item->getValue() ,
                'action'=>
                '<a href="'.$linkDetail.'" class="btn btn-info"><i class="icon-info-sign"></i></a>
                 <a href="'.$linkEdit.'" class="btn btn-primary"><i class="icon-edit-sign"></i></a>
                 <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }


    public function indexAction()
    {
        return new ViewModel(array(
            'title'=>$this->translator->translate('Supplier item')));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $event = new SupplierItem();
            $configForm = new supplieritemForm();
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $event->setValue($data['value']);
                $event->setIsdelete(0);
                $inserted= $this->modelSubItem->insert($event);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'supplieritem'));

            }

            return new ViewModel(array(
                'data' =>$event,
                'title' => $this->translator->translate('Add new supplier item'),
                'form' => $configForm
            ));
        }
        else{
            $event = $this->modelSubItem->findOneBy(array('id'=>$id));

            $configForm = new supplieritemForm();
            $configForm->setAttribute('action', '/admin/supplieritem/add/'.$id);

            $configForm->get('id')->setValue($event->getId());
            $configForm->get('value')->setValue($event->getValue());

            if($request->isPost()){
                $data = $this->params()->fromPost();

                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelSubItem->findOneBy(array('id'=>$idFormPost));
                $event->setValue($data['value']);
                $event->setIsdelete(0);
                $this->modelSubItem->edit($event);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'supplieritem'));

            }
            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit event: '.$event->getValue(),
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
            $event = $this->modelSubItem->findOneBy(array('id'=>$id));

            $event->setIsdelete(1);
            $this->modelSubItem->edit($event);
            $this->modelSubItemFor->deleteAll(array('supplierItem'=>$id));
            echo 1;
        }
        die;

    }

    public function detailAction(){
        $id = $this->params()->fromRoute('id');
        $orderInfo = $this->modelSubItem->findOneBy(array('id'=>$id));
        $dataRow = $this->modelSubItem->convertSingleToArray($orderInfo);
        $orderDetails = $this->modelSubItem->findBy(array('isdelete'=>0,'id'=>$id));
        $dataOrder =  array(
            'title'=> $this->translator->translate('Detail').': #'.$orderInfo->getId(),
            'link' => 'admin/supplieritem',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'value' => $this->translator->translate('Value'),

            )
        );

        return new ViewModel(array('data'=>$dataOrder));
    }

    private function parseToArraySelect($data){
        $array = array();
        foreach($data as $item){
            $array[$item['id']] = $item['id'];
        }
        return $array;
    }


} 