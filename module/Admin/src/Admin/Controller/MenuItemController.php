<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/6/14
 * Time: 11:26 AM
 */

namespace Admin\Controller;
use Admin\Entity\MenuItem;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Entity\Table;
use Admin\Entity\User;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Admin\Model\menuItemModel;

class MenuItemController extends AbstractActionController {

    protected   $modelMenuItem;
    protected   $translator;

    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $manageTable = $this->sm->get($service_locator_str);
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

        $this->modelMenuItem = new menuItemModel($manageTable);
        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        $table = $this->modelMenuItem->findAll();


        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelMenuItem->convertToArray($table);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manager menu item'),
            'link' => 'admin/table',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'name' => $this->translator->translate('Name')
            ),
            'hideDetailButton' => 1
        );
        return new ViewModel(array('data'=>$data,'title'=> $this->translator->translate('Table')));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            if($request->isPost()) {
                $menuItem = new MenuItem();
                $menuItem->setMenuStoreId($this->params()->fromPost('menu_store_id'));
                $menuItem->setMenuId($this->params()->fromPost('menu_id'));
                $menuItem->setQuantity($this->params()->fromPost('quantity'));
                $menuItem->setUnit($this->params()->fromPost('unit'));
                $tableInserted = $this->modelMenuItem->insert($menuItem);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'menuitem'));
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'table'));
            return new ViewModel(array('title'=>$this->translator->translate('Add new table')));
        }
        else{

            $menuItem = $this->modelMenuItem->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $idFormPost = $this->params()->fromPost('id');
                $menuItem = $this->modelMenuItem->findOneBy(array('id'=>$idFormPost));
                $menuItem->setName($this->params()->fromPost('name'));
                $menuItem->setMenuStoreId($this->params()->fromPost('menu_store_id'));
                $menuItem->setMenuId($this->params()->fromPost('menu_id'));
                $menuItem->setQuantity($this->params()->fromPost('quantity'));
                $menuItem->setUnit($this->params()->fromPost('unit'));
                $this->modelMenuItem->edit($menuItem);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'menuitem'));
            }
            return new ViewModel(array(
                'data' =>$menuItem,
                'title' => $this->translator->translate('Edit table').': '.$menuItem->getId()
            ));
        }
    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
//            $table = $this->modelMenuItem->findOneBy(array('id'=>$id));
//            $table->setIsdelete(1);
            $this->modelMenuItem->delete(array('id'=>$id));
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
} 