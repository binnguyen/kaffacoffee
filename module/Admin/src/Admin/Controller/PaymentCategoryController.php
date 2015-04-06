<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;

use Velacolib\Utility\Table;
use Velacolib\Utility\Table\AjaxTable;
use Velacolib\Utility\Table\Detail;


use Admin\Entity\Categories;
use Admin\Entity\PaymentCategory;
use Admin\Form\paymentCategoryForm;
use Admin\Model\categoryModel;
use Admin\Model\paymentCategoryModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;



class PaymentCategoryController extends AdminGlobalController
{
    protected   $modelCategories;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $CategoriesTable = $this->sm->get($service_locator_str);
        $this->modelCategories = new paymentCategoryModel($CategoriesTable);
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

        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name', 'dt' => 1, 'search'=>false, 'type' => 'text' ),
            array('title' =>'Action', 'db' => 'id', 'dt' => 2, 'search'=>false, 'type' => 'text','formatter'=>function($d,$row){

                $actionUrl = '/admin/payment-category';
                return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';

            } ),
        );

        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/payment-category');
        $table->setTablePrefix('ts');
        $table->setAjaxCall('/admin/payment-category');
        $this->tableAjaxRequest($table,$columns,$this->modelCategories);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Manage Payment Category')));

    }





    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');

        if($id == ''){

            $customer = new PaymentCategory();
            $customerForm = new paymentCategoryForm();

            if($request->isPost()){
                $data = $this->params()->fromPost();

                $customer->setName($data['name']);
                $customer->setIsdelete(0);

                $this->modelCategories->insert($customer);

            }
            return new ViewModel(array(
                'data' =>$customer,
                'title' => 'Edit accrued: '.$customer->getName(),
                'form' => $customerForm
            ));

        } else{

            $event = $this->modelCategories->findOneBy(array('id'=>$id));
            $configForm = new paymentCategoryForm();
            $configForm->setAttribute('action', '/admin/payment-category/add/'.$id);
            $configForm->get('id')->setValue($event->getId());
            $configForm->get('name')->setValue($event->getName());


            if($request->isPost()){

                $data = $this->params()->fromPost();
                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelCategories->findOneBy(array('id'=>$idFormPost));
                $event->setName($data['name']);
                $event->setIsdelete(0);
                $this->modelCategories->edit($event);
                //update form

                $configForm->get('name')->setValue($event->getName());
                $this->flashMessenger()->addSuccessMessage("Save change...");
            }

            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit accrued: '.$event->getName(),
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


    }

}