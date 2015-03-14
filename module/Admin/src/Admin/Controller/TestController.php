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
use Velacolib\Utility\TransactionUtility;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class TestController extends AbstractActionController
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
      //  TransactionUtility::updateQuantityMenuItemInStore(1,ADD_ORDER,ADD_ORDER_ACTION);
        die;
        $data['menuStoreId'] = 1;
        $data['quantity'] = -10;
        $data['action'] = 'X';
        $data['unit'] = 'KG';
        $data['menuId'] = 1;
        TransactionUtility::insertTransaction($data);
        die;
    }
}