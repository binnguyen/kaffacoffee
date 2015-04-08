<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Frontend\Controller;
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
    protected  $googleClientId = '927311531718-rps1cvcjnd2m6g5m3133gu0m8t7pq1m1.apps.googleusercontent.com';
    protected  $googleClientSecret = '60vBHx8SD5QJ9fLwntRxCUW8';
    protected  $googleRedirectUri = 'http://cafe.info/frontend/login/google';
    protected  $googleDeveloperKey = ' AIzaSyAE0KDF75g_5JdL9RXFcQIS-4KFqRnILvw';
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
            'link' => '/frontend/login',
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
                $this->redirect()->toRoute('frontend/child',array('controller'=>'table'));
            }
            else
                $this->redirect()->toRoute('frontend/child',array('controller'=>'login'));
            //end check login

        }
        return new ViewModel($data);
    }
    public function logoutAction(){
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        $this->redirect()->toRoute('frontend/child',array('controller'=>'login'));
    }

    public function googleAction(){

        $googleClient = new \Google_Client();
        $googleClient->setClientId($this->googleClientId);
        $googleClient->setClientSecret($this->googleClientSecret);
        $googleClient->setDeveloperKey($this->googleDeveloperKey);
        $googleClient->setRedirectUri($this->googleRedirectUri);
        $googleClient->setScopes(array('email'));
//
//        $googleOauthV2 = new \Google_Auth_OAuth2($googleClient);
        if(isset($_REQUEST['token']) || isset($_REQUEST['code']) || isset($_REQUEST['state']) ){
         $googleClient->authenticate($_REQUEST['code']);
         $token = $googleClient->getAccessToken() ;
            if($token){
                $tokenData = $googleClient->verifyIdToken()->getAttributes();
                print_r($tokenData);die;
            }
        }

    }

}