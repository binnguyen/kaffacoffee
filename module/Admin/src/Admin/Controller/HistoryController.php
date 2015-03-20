<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Categories;
use Admin\Entity\UserHistory;
use Admin\Entity\User;
use Admin\Model\userHistoryModel;
use Velacolib\Utility\Utility;
use Admin\Model\userModel;
use Zend\View\Model\ViewModel;
use Velacolib\Utility\Table\AjaxTable;
use Zend\Mvc\Controller\AbstractActionController;


class HistoryController extends AdminGlobalController
{
    protected   $modelHistory;
    protected   $translator;
    public function init(){

        $this->modelHistory = new userHistoryModel($this->doctrineService);
    }



    public function indexAction()
    {
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0,'prefix' => 'h' ,'select' => 'id', 'search'=>false, 'type' => 'number' ),
            array('title' =>'User', 'db' => 'userName', 'prefix' => 'u' ,'select' => 'userName', 'dt' => 1, 'search'=>true, 'type' => 'text'),
            array('title' =>'Action', 'db' => 'action','prefix' => 'h', 'select'=>'action','dt' => 2, 'search'=>false, 'type' => 'number'),
            array('title' =>'Time', 'db' => 'time','prefix'=>'h','select'=>'time', 'dt' => 3, 'search'=>false, 'type' => 'number',
                'formatter' => function($d,$row){
                    return date('d-m-Y h:i:s',$d);
                }
            ),
        );

        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/history');
        $table->setTablePrefix('h');
        $table->setExtendJoin(
            array(
                array('Admin\Entity\User','u','with','h.userId = u.id')
            )
        );

        $table->setAjaxCall('/admin/history');
        $this->tableAjaxRequest($table,$columns,$this->modelHistory);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('User History')));

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
                $userInserted = $this->modelUsers->insert($cat);
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
//            $menu = $this->modelUsers->findOneBy(array('id'=>$id));
//            $menu->setIsdelete(1);
//            $this->modelUsers->edit($menu);
            $this->modelUsers->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }

    public function editAction()
    {


    }

}