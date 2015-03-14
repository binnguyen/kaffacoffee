<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/4/14
 * Time: 9:43 AM
 */

namespace Admin\Controller;
use Admin\Entity\Surtax;
use Admin\Model\surTaxModel;
use Admin\Model\menuModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class SurtaxController extends BaseController {


    protected   $modelSurTax;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelSurTax = new surTaxModel($doctrine);
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
        $dql = "SELECT c FROM Admin\Entity\Surtax c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Surtax c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();
        // map data
        $ret = array_map(function($item) {

            // create link
            $linkEdit =   '/admin/surtax/add/'.$item->getId() ;
            $linkDelete =  '/admin/surtax/delete/'.$item->getId() ;
            $linkDetail =   '/admin/surtax/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'value' => $item->getValue(),
                'type' => $item->getType(),
                'action'=> '

                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
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
            if($request->isPost()) {
                $data = $this->params()->fromPost();
                $surtax = new Surtax();
                $surtax->setName($data['name']);
                $surtax->setValue($data['value']);
                $surtax->setType($data['type']);
                $this->modelSurTax->insert($surtax);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'surtax'));
            }
            //insert new user

            return new ViewModel(array('title'=> $this->translator->translate('Add new surtax')));
        }
        else{

            $surtax = $this->modelSurTax->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $idFormPost = $data['id'];
                $surtax = $this->modelSurTax->findOneBy(array('id'=>$idFormPost));
                $surtax->setName($data['name']);
                $surtax->setValue($data['value']);
                $surtax->setType($data['type']);
                $this->modelSurTax->edit($surtax);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'surtax'));
            }

            return new ViewModel(array(
                'data' =>$surtax,
                'title' => $this->translator->translate('Edit Surtax:')
            ));
        }
    }

    public function detail(){

    }

    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->params()->fromPost('id');
//            $menu = $this->modelSurTax->findOneBy(array('id' => $id));
//            $menu->setIsdelete(1);
//            $this->modelSurTax->edit($menu);
            $this->modelSurTax->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
} 