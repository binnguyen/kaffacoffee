<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Property;
use Admin\Entity\Table;
use Admin\Form\propertyForm;

use Admin\Model\propertyModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class PropertyController extends BaseController
{
    protected   $modelProperty;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelProperty = new propertyModel($doctrine);
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
            'unit',
            'des',
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
        $dql = "SELECT c FROM Admin\Entity\Property c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Property c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();
        // map data
        $ret = array_map(function($item) {

            // create link
            $linkEdit =   '/admin/property/add/'.$item->getId() ;
            $linkDelete =  '/admin/property/delete/'.$item->getId() ;
            $linkDetail =   '/admin/property/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'unit' => $item->getUnit(),
                'des' => $item->getDes(),
                'action'=> '

                 <a target="_blank" href="'.$linkEdit.'" class="btn btn-primary"><i class="icon-edit-sign"></i></a>
                 <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete" ><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }

    public function indexAction()
    {
        return new ViewModel(array(
            'title'=>$this->translator->translate('Property manager')
        ));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $configForm = new propertyForm();
            $configForm->setAttribute('action', '/admin/property/add');

            if($request->isPost()){
                $data = $this->params()->fromPost();
                $cat = new Property();
                $cat->setName($data['name']);
                $cat->setQuantity($data['quantity']);
                $cat->setUnit($data['unit']);
                $cat->setDes($data['des']);
                $this->modelProperty->edit($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'property'));
            }

            return new ViewModel(array(
                'form'=>$configForm ,
                'title'=>$this->translator->translate('Property'),

            ));

        }
        else{
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelProperty->findOneBy(array('id'=>$idFormPost));
                $cat->setName($data['name']);
                $cat->setQuantity($data['quantity']);
                $cat->setUnit($data['unit']);
                $cat->setDes($data['des']);
                $this->modelProperty->edit($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'property'));
            }
            $config = $this->modelProperty->findOneBy(array('id'=>$id));
            $configForm = new propertyForm();
            $configForm->setAttribute('action', '/admin/property/add/'.$id);
            $configForm->get('id')->setValue($config->getId());
            $configForm->get('name')->setValue($config->getName());
            $configForm->get('quantity')->setValue($config->getQuantity());
            $configForm->get('unit')->setValue($config->getUnit());
            $configForm->get('des')->setValue($config->getDes());
            return new ViewModel(array(
                'data' =>$config,
                'title' => $this->translator->translate('Edit').' '.$config->getName(),
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
            $combo = $this->modelCombo->findOneBy(array('id'=>$id));
            $combo->setIsdelete(1);
            $this->modelCombo->edit($combo);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {
    }

}