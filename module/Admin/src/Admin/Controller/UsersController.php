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
use Velacolib\Utility\Table\AjaxTable;
use Zend\Mvc\Controller\AbstractActionController;

use Zend\Authentication\AuthenticationService;

class UsersController extends AdminGlobalController
{
    protected   $modelUsers;
    protected   $translator;
    public function init(){
        $this->modelUsers = new userModel($this->doctrineService);

    }
    public function indexAction()
    {
        //config table
        /////column for table
        $menuTableColumn  =  $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>true, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'userName','dt' => 1, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Full name', 'db' => 'fullName','dt' => 2, 'search'=>true, 'type' => 'number' ),
            array('title' =>'User type', 'db' => 'type','dt' => 3, 'search'=>true, 'type' => 'number',
                'dataSelect' => Utility::getUserRole()
            ),
            array('title' =>'Action', 'db' => 'id','dt' => 4, 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/users';
                    return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                         <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';
                }
            )

        );
        /////end column for table
        $table = new AjaxTable($menuTableColumn, 'admin/users');
        $table->setTablePrefix('u');
        $table->setExtendSQl(
            array(
                array('AND','u.isdelete','=','0')
            )
        );
        $table->setAjaxCall('/admin/users');
        $table->setActionLink('admin/users');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelUsers);
        //end config table

        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Manage Users')));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            if($request->isPost()) {
                $checkExist = Utility::checkUserExist($this->params()->fromPost('userName'));
                if($checkExist==true)
                {
                    $this->flashMessenger()->addSuccessMessage("User existed");
                    return    $this->redirect()->toRoute('admin/child',array('controller'=>'users','action'=>'add'));
                }
                $cat = new User();
                $cat->setUserName($this->params()->fromPost('userName'));
                $cat->setFullName($this->params()->fromPost('fullName'));
                $cat->setPassword(sha1($this->params()->fromPost('password')));
                $cat->setIsdelete(0);
                $cat->setType($this->params()->fromPost('usertype'));
                $userInserted = $this->modelUsers->insert($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                return  $this->redirect()->toRoute('admin/child',array('controller'=>'users'));
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            return new ViewModel(array('title'=> $this->translator->translate('Add new user')));
        }
        //update
        else{

            $cat = $this->modelUsers->findOneBy(array('id'=>$id));
            if($request->isPost()){
                //check exist
                $checkExist = Utility::checkUserExist($this->params()->fromPost('userName'));
                if($checkExist==true)
                {
                    $this->flashMessenger()->addSuccessMessage("User existed");
                    return    $this->redirect()->toRoute('admin/child',array('controller'=>'users','action'=>'add','id'=>$cat->getId()));
                }
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelUsers->findOneBy(array('id'=>$idFormPost));
                $cat->setUserName($this->params()->fromPost('userName'));
                $cat->setFullName($this->params()->fromPost('fullName'));
                $cat->setType($this->params()->fromPost('usertype'));
                if($this->params()->fromPost('password') != ''){
                    $cat->setPassword(sha1($this->params()->fromPost('password')));
                }

                $this->modelUsers->edit($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                return $this->redirect()->toRoute('admin/child',array('controller'=>'users'));
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