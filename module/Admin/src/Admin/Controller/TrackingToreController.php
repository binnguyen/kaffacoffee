<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/4/14
 * Time: 9:43 AM
 */

namespace Admin\Controller;
use Admin\Entity\Surtax;
use Admin\Entity\TrackingTore;
use Admin\Form\trackingToreForm;
use Admin\Model\surTaxModel;
use Admin\Model\menuModel;
use Admin\Model\trackingToreModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class TrackingToreController extends BaseController {


    protected   $modelTracking;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelTracking = new trackingToreModel($doctrine);
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
            'name',
            'quantity',
            'supplierItemId',
            'supplierItemName',
            'note',
            'time',
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
        $dql = "SELECT c FROM Admin\Entity\TrackingTore c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\TrackingTore c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();
        // map data
        $ret = array_map(function($item) {

            // create link
            $linkEdit =   '/admin/tracking-tore/add/'.$item->getId() ;
            $linkDelete =  '/admin/tracking-tore/delete/'.$item->getId() ;
            $linkDetail =   '/admin/tracking-tore/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'supplierItemId' => $item->getSupplierItemId(),
                'supplierItemName' => $item->getSupplierItemName(),
                'note' => $item->getNote(),
                'time' => $item->getTime(),
                'action'=> '

                 <a href="'.$linkEdit.'" class="btn btn-primary"><i class="icon-edit-sign"></i></a>
                 <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete" ><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }


    public function indexAction(){
        return new ViewModel(array('title'=>$this->translator->translate('Surtax')));
    }

    public function addAction(){
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $configForm = new trackingToreForm();
            $configForm->setAttribute('action', '/admin/tracking-tore/add');

            if($request->isPost()) {
                $data = $this->params()->fromPost();
                $tracking = new TrackingTore();
                $tracking->setName($data['name']);
                $tracking->setQuantity($data['quantity']);
                $tracking->setSupplierItemId($data['supplierItemId']);
                $tracking->setSupplierItemName($data['supplierItemName']);
                $tracking->setNote($data['note']);
                $tracking->setTime(date('Y-m-d',strtotime($data['time'])));
                $this->modelTracking->insert($tracking);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert tracking success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'tracking-tore'));
            }
            //insert new user

            return new ViewModel(array(
                'title'=> $this->translator->translate('Add new tracking'),
                'form'=>$configForm
            ));
        }
        else{

            $surtax = $this->modelTracking->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $idFormPost = $data['id'];
                $tracking = $this->modelTracking->findOneBy(array('id'=>$idFormPost));
                $tracking->setName($data['name']);
                $tracking->setQuantity($data['quantity']);
                $tracking->setSupplierItemId($data['supplierItemId']);
                $tracking->setSupplierItemName($data['supplierItemName']);
                $tracking->setNote($data['note']);
                $tracking->setTime(date('Y-m-d H:i:s',$data['time']));
                $this->modelTracking->edit($tracking);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'tracking-tore'));
            }
            $config = $this->modelTracking->findOneBy(array('id'=>$id));
            $configForm = new trackingToreForm();
            $configForm->setAttribute('action', '/admin/tracking-tore/add/'.$id);
            $configForm->get('id')->setValue($config->getId());
            $configForm->get('name')->setValue($config->getName());
            $configForm->get('quantity')->setValue($config->getQuantity());
            $configForm->get('supplierItemId')->setValue($config->getSupplierItemId());
            $configForm->get('supplierItemName')->setValue($config->getSupplierItemName());
            $configForm->get('note')->setValue($config->getNote());
            $configForm->get('time')->setValue($config->getTime());

            return new ViewModel(array(
                'data' =>$surtax,
                'title' => $this->translator->translate('Edit tracking:') ,
                'form'  =>$configForm
            ));

        }
    }

    public function detail(){

    }

    public function deleteAction()
    {
        //get user by id
            $id = $this->params()->fromRoute('id');
          //  $menu = $this->modelTracking->findOneBy(array('id' => $id));
           // $menu->setIsdelete(1);
          //  $this->modelTracking->edit($menu);
            $this->modelTracking->delete(array('id'=>$id));
            $this->flashMessenger()->addSuccessMessage('Delete success');
            $this->redirect()->toRoute('admin/child',array(
                'controller'=>'tracking-tore',
                ));

    }
} 