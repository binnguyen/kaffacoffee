<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Table;
use Admin\Entity\User;
use Admin\Model\userModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;


class LoginController extends AbstractActionController
{
    protected   $modelUsers;
    protected   $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){
        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $CategoriesTable = $this->sm->get($service_locator_str);
        $this->modelUsers = new userModel($CategoriesTable);
        $this->translator = Utility::translate();
        $this->layout('layout/login');
        //$this->layout('layout/login');
        return parent::onDispatch($e);
    }
    public function indexAction()
    {
        //$this->modelUsers->createQuery('');

        $users = $this->modelUsers->findBy(array('isdelete'=>'0'));
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelUsers->convertToArray($users);
        $data =  array(
            'title'=> $this->translator->translate('Login'),
            'link' => '/admin/login',
            'buttonLogin' => $this->translator->translate('Login'),
            'userNameText' => $this->translator->translate('User name'),
            'passwordText' => $this->translator->translate('Password'),
        );
        if($this->getRequest()->isPost()){
            $userName = $this->params()->fromPost('userName');
            $password = $this->params()->fromPost('password');
            $data = $this->params()->fromPost();

            //login here
            $login_obj =  new AuthenticationService(null,$this->modelUsers);
            $this->modelUsers->setLoginUser($data);
            $login_obj->authenticate();



            //check login
            $user = Utility::checkLogin($this);
            if($user!=null){
                Utility::insertHistory('login');
                $this->redirect()->toRoute('admin/child',array('controller'=>'dashboard'));
            }

            else
                $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
            //end check login

        }
        return new ViewModel($data);
    }
    public function logoutAction(){
        Utility::insertHistory('logout');
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
    }

}