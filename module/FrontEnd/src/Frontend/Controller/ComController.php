<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Frontend\Controller;
use Admin\Entity\Categories;
use Admin\Entity\Table;
use Velacolib\Utility\Table\AjaxTable;
use Admin\Model\categoryModel;
use Admin\Model\comboModel;
use Admin\Model\menuModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class ComController extends FrontEndController
{
    protected   $modelCombo;
    protected   $modelMenu;
    protected   $translator;
    public function init(){

        $this->modelCombo = new comboModel($this->doctrineService);
        $this->modelMenu = new menuModel($this->doctrineService);
    }
    public function indexAction()
    {
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Category', 'db' => 'catId','dt' => 2, 'search'=>false, 'type' => 'number',
                'dataSelect' => Utility::getCategoryForSelect()
            ),
            array('title' =>'Cost', 'db' => 'cost','dt' => 3, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Take Away cost', 'db' => 'taCost','dt' => 4, 'search'=>false, 'type' => 'number' ),

        );
        /////end column for table
        $table = new AjaxTable($columns, array(), 'frontend/com');
        $table->setTablePrefix('m');
        $table->setExtendSQl(
            array(
                array('AND','m.isdelete','=','0'),
                array('AND','m.isCombo','=','1'),
            )
        );
        $table->setAjaxCall('/frontend/com');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelMenu);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Combo')));
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