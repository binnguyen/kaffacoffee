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
use Admin\Model\categoryModel;
use Admin\Model\comboModel;
use Admin\Model\menuModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class ComController extends AbstractActionController
{
    protected   $modelCombo;
    protected   $modelMenu;
    protected   $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelCombo = new comboModel($doctrine);
        $this->modelMenu = new menuModel($doctrine);
        $this->translator = Utility::translate();

        //check login
        $user = Utility::checkLogin($this);
        if(! is_object($user) && $user == 0){
            $this->redirect()->toRoute('frontend/child',array('controller'=>'login'));
        }
        //end check login

        return parent::onDispatch($e);
    }
    public function indexAction()
    {
        $combos = $this->modelMenu->findBy(array('isdelete'=>'0','isCombo'=>1));
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelMenu->convertToArray($combos);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manage combo'),
            'link' => 'frontend/index',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'name' => $this->translator->translate('Name'),
                'cost' => $this->translator->translate('Cost'),
                'taCost' => $this->translator->translate('Take away'),
                'catId' => $this->translator->translate('Category'),
                'isCombo' => $this->translator->translate('Combo'),
                'desc' => $this->translator->translate('Desc'),
//                'image' => 'Image',
            ),
            'hideDeleteButton' => 1,
            'hideDetailButton' => 0,
            'hideEditButton' => 1
        );

        return new ViewModel(array('data'=>$data ,'title'=> $this->translator->translate('Manage combo')));
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