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
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;



class CategoryController extends BaseController
{
    protected   $modelCategories;
    protected  $translator;

    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $CategoriesTable = $this->sm->get($service_locator_str);
        $this->modelCategories = new categoryModel($CategoriesTable);
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
//        $categories = $this->modelCategories->findBy(array('isdelete'=>'0'));
//        //tableTitle = table heading
//        //datarow row of table... render by heading key
//        //heading key = table column name
//        $dataRow = $this->modelCategories->convertToArray($categories);
//        $data =  array(
//            'tableTitle'=> $this->translator->translate('Manage categories'),
//            'link' => 'admin/category',
//            'data' =>$dataRow,
//            'heading' => array(
//                'id' => 'Id',
//                'name' => $this->translator->translate('Name')
//            ),
//            'hideDetailButton' => 1
//        );
        return new ViewModel(array('title'=> $this->translator->translate('Category')));
    }



    public function ajaxListAction()
    {

        $fields = array(
            'id',
            'name',
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

        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\Categories c";
        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery .$dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Categories c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        $ret = array_map(function($item) {

            $linkEdit =   '/admin/category/add/'.$item->getId() ;
            $linkDelete =  '/admin/category/delete/'.$item->getId() ;
            $linkDetail =   '/admin/category/detail/'.$item->getId() ;

            return array(
                'DT_RowId'=> 'rowID_'.$item->getId(),
                'id' => $item->getId(),
                'name' => $item->getName() ,
                'action'=> '
                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="#" class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>'
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

}