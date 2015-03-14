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
use Admin\Entity\User;
use Velacolib\Utility\Utility;
use Admin\Model\userModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

use Zend\Authentication\AuthenticationService;

class UsersController extends BaseController
{
    protected   $modelUsers;
    protected   $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $CategoriesTable = $this->sm->get($service_locator_str);
        $this->modelUsers = new userModel($CategoriesTable);
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

        return new ViewModel(array('title'=>$this->translator->translate('Users')));
    }

    public function ajaxListAction(){
        $fields = array(
            'id',
            'userName',
            'fullName',
            'type',
            'apiKey',
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
        $dql = "SELECT c FROM Admin\Entity\User c ";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\User c ";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();
        // map data
        $ret = array_map(function($item) {
            // create link
            $linkEdit =   '/admin/users/add/'.$item->getId() ;
            $linkDelete =  '/admin/users/delete/'.$item->getId() ;
            $linkDetail =   '/admin/users/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'userName' => $item->getUserName(),
                'fullName' => $item->getFullName(),
                'type' => Utility::getUserRole($item->getType()),
                'apiKey' => $item->getApiKey(),
                'action'=> '
                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete" ><i class="icon-trash"></i></a>'
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
                $cat = new User();
                $cat->setUserName($this->params()->fromPost('userName'));
                $cat->setFullName($this->params()->fromPost('fullName'));
                $cat->setPassword(sha1($this->params()->fromPost('password')));
                $cat->setIsdelete(0);
                $cat->setType(0);
                $cat->setApiKey(md5($this->params()->fromPost('userName').API_STRING));
                $userInserted = $this->modelUsers->insert($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'users'));
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            return new ViewModel(array('title'=> $this->translator->translate('Add new user')));
        }
        //update
        else{

            $cat = $this->modelUsers->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelUsers->findOneBy(array('id'=>$idFormPost));
                $cat->setUserName($this->params()->fromPost('userName'));
                $cat->setFullName($this->params()->fromPost('fullName'));
                if($this->params()->fromPost('password') != ''){
                    $cat->setPassword(sha1($this->params()->fromPost('password')));
                }

                $this->modelUsers->edit($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'users'));
            }
            return new ViewModel(array(
                'data' =>$cat,
                'title' => $this->translator->translate('Edit User:').$cat->getUserName()
            ));
        }
    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $menu = $this->modelUsers->findOneBy(array('id'=>$id));
            $menu->setIsdelete(1);
            $this->modelUsers->edit($menu);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {


    }
    public function testAction(){
        $Auth_service = new AuthenticationService();
        $auth = $Auth_service->getIdentity();
        echo '<pre>';
        print_r($auth);die;
    }
}