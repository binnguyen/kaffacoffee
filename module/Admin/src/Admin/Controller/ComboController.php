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
use Admin\Model\categoryModel;
use Admin\Model\comboModel;
use Admin\Model\menuModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class ComboController extends BaseController
{
    protected   $modelCombo;
    protected   $modelMenu;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelCombo = new comboModel($doctrine);
        $this->modelMenu = new menuModel($doctrine);
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


    public function indexAction()
    {
        return new ViewModel(array('title'=>$this->translator->translate('Manage Combo')));
    }

    public function ajaxListAction()
    {

        $fields = array(
            'id',
            'menuParentId',
            'menuChildId',
            'menuQuantity',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = ' c.isdelete = 0 ';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }

        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\MenuCombo c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql .$customQuery .$dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\MenuCombo c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        $ret = array_map(function($item) {
            $linkEdit =   '/admin/index/add/'.$item->getMenuParentId() ;
            $linkDelete =  '/admin/index/delete/'.$item->getId() ;
            $linkDetail =   '/admin/index/detail/'.$item->getMenuParentId() ;

            return array(
                'id' => $item->getId(),
                'menuParentId' => $item->getMenuParentId()  ,
                'menuChildId' => $item->getMenuChildId()  ,
                'menuQuantity' => $item->getMenuQuantity()  ,
                'action'=>'
                 <a target="_blank" href="'.$linkDetail.'" class="btn btn-info"><i class="icon-info-sign"></i></a>
                 <a target="_blank" href="'.$linkEdit.'" class="btn btn-primary"><i class="icon-edit-sign"></i></a>
                 <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }



    public function addAction()
    {
//        $request = $this->getRequest();
//        $id = $this->params()->fromRoute('id');
//        //insert
//        if($id == ''){
//            if($request->isPost()) {
//                $cat = new Categories();
//                $cat->setName($this->params()->fromPost('name'));
//                $catInserted = $this->modelCategories->insert($cat);
//            }
//            //insert new user
//            //$this->redirect()->toRoute('admin/child',array('controller'=>'category'));
//            return new ViewModel(array('title'=>'Add New Category'));
//        }
//        else{
//
//            $cat = $this->modelCategories->findOneBy(array('id'=>$id));
//            if($request->isPost()){
//                $idFormPost = $this->params()->fromPost('id');
//                $cat = $this->modelCategories->findOneBy(array('id'=>$idFormPost));
//                $cat->setName($this->params()->fromPost('name'));
//                $this->modelCategories->edit($cat);
//            }
//            return new ViewModel(array(
//                'data' =>$cat,
//                'title' => 'Edit Category: '.$cat->getName()
//            ));
//        }
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
//        //get user by id
//        $id = $this->params()->fromRoute('id');
//        $user = $this->model->findOneBy(array('id'=>$id));
//        $user->setFullName('tri 1234');
//        $this->model->edit($user);
//        //update user

    }

}