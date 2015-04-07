<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 12/11/2014
 * Time: 9:32 AM
 */

namespace Frontend\Controller;
use Velacolib\Utility\setupUtility;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

abstract class FrontEndController extends AbstractActionController{
    protected $translator;
    protected  $serviceLocatorStr;
    protected  $doctrineService;

    //not edit ini onDispatch
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $install = setupUtility::checkInstall();
        if($install == true){
            return $this->redirect()->toRoute('install');
        }
        //get doctrine service
        $this->serviceLocatorStr = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $this->doctrineService = $this->sm->get($this->serviceLocatorStr);
        //get translate service
        $this->translator = Utility::translate();
        //check login
        $user = Utility::checkLogin();
        if (!is_object($user) && $user == 0) {
            $this->redirect()->toRoute('frontend/child', array('controller' => 'login'));
        }

        //start acl
//        $acl = new myAcl();
//        $currentRoute  =  $this->getModuleCurrentRoute($e);
//        $isOk = $acl->checkRole(UtilityRoleLevel::convertUserTypeToRole($user->userType)['role'],$currentRoute);
//        if(!$isOk || $isOk == '' || $isOk == null){
//           return $this->redirect()->toRoute('frontend/child', array('controller' => 'login'));
//        }
        //end check login
        //end acl

        $this->init();

        return parent::onDispatch($e);
    }

    //supply init for child class edit
    protected function init(){}
    public function indexAction(){}
    public function addAction(){}
    public function deleteAction(){}
    public function detailAction(){}

    //get ajax request for table
    protected function tableAjaxRequest($table, $columns, $dataModel){

        //test

        //test

        if ($this->getRequest()->isXmlHttpRequest()){
            $table->setDataModel($dataModel);
            $request = $this->params()->fromQuery();
            echo json_encode($table->getDataTableAjax($request,$columns));
            die;
        }
    }
    protected  function getModuleCurrentRoute($e){
        $returnArray = array();
        $controller = $e->getTarget();
        $controllerClass = get_class($controller);
        $moduleName = strtolower(substr($controllerClass, 0, strpos($controllerClass, '\\')));
        $routeMatch = $e->getRouteMatch();
        $actionName = strtolower($routeMatch->getParam('action', 'not-found')); // get the action name
        $controllerName = $routeMatch->getParam('controller', 'not-found');     // get the controller name
        $controllerName = strtolower(array_pop(explode('\\', $controllerName)));
        $returnArray = array(
            'module'=>$moduleName,
            'controller'=>$controllerName,
            'action' => $actionName);
        return $returnArray;
    }
}