<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Install\Controller;
use Install\Form\InstallForm;
use Install\Form\InstallForm2;
use Install\Form\InstallForm3;
use Velacolib\Utility\Utility;
use Velacolib\Utility\setupUtility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    protected   $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){
        $this->translator = Utility::translate();

        $this->layout('layout/install/installlayout');
        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        $port = ini_get('mysql.default_port');
        $port = isset($port)?$port:'3306';
        $this->flashMessenger()->clearMessages();
        $writeables = setupUtility::checkWriteable();
        $isWriteables = true;
        foreach($writeables as $k => $value){
            if(!$value)
                $isWriteables = false;
        }
        $installForm = new InstallForm();
        $installForm->get('host')->setValue('localhost');
        $installForm->get('port')->setValue($port);
        $installForm->get('username')->setValue('');
        $installForm->get('password')->setValue('');
        $installForm->get('dbname')->setValue('');
        if($isWriteables == false){
            $installForm->get('send')->setAttributes(array('disabled'=>'true'));
        }
        $request = $this->getRequest();

        if($request->isPost()){
            $data = $this->params()->fromPost();
            if($data['host'] == '' || $data['username'] == ''  || $data['dbname']  == '' ){
                $this->flashMessenger()->addErrorMessage("Please fill out all info");
                $this->redirect()->toUrl('install/index');
            }else{
                //write config file

                $connection = mysql_connect($data['host'],$data['username'],$data['password']);
                $selectDB = mysql_select_db($data['dbname']);

                if(!$connection || !$selectDB){
                    $this->flashMessenger()->addErrorMessage("Could not connect to mysql host. Wrong username or password, Please try again!");
                    return $this->redirect()->toRoute('install',array('controller'=>'install','action'=>'index'));
                }else{
                    $result =  setupUtility::createConfigFile($data);
                    //end write config file

                    if($result == true)
                    {
                        $this->flashMessenger()->addSuccessMessage("Config file created");
                        return $this->redirect()->toRoute('install',array('controller'=>'install','action'=>'installstep2'));
                    }
                    else{
                        $this->flashMessenger()->addErrorMessage("Could not connect to mysql host. Wrong DatabaseName, Please try again!");
                        // return $this->redirect()->toRoute('install',array('controller'=>'install','action'=>'index'));
                    }
                    //finish create config file

                }


            }
        }
        return new ViewModel(
            array(
                'title' => array(
                    'title'=>$this->translator->translate('Install')
                ),
                'form' => $installForm,
                'writeable' => $writeables)
        );

    }
    public function installstep2Action(){
        if(!setupUtility::checkConfigFile())
            return  $this->redirect()->toRoute('install');
        $installForm2 = new InstallForm2();
        $installForm2->get('adminName')->setValue('admin');
        $installForm2->get('adminPassword1')->setValue('admin');
        $installForm2->get('adminPassword2')->setValue('admin');
        $request = $this->getRequest();
        if($request->isPost()){
            $data = $this->params()->fromPost();
            if($data['adminName'] == '' || $data['adminPassword1'] == '' || $data['adminPassword2'] == '' ){
                $this->flashMessenger()->addErrorMessage("Please fill out all info");
            }else{
                if($data['adminPassword1'] != $data['adminPassword2']){
                    $this->flashMessenger()->addErrorMessage("These passwords don't match. Try again?");
                    return $this->redirect()->toRoute('install',array('controller'=>'install','action'=>'installstep2'));
                }
                else{
                    $resault = setupUtility::createDatabase($data);
                    if($resault == null)
                        $this->redirect()->toRoute('install');
                    $this->flashMessenger()->addSuccessMessage("Data inserted");
                    return $this->redirect()->toRoute('install',array('controller'=>'install','action'=>'installstep3'));
                }
            }
        }
        return  new ViewModel(
            array(  'title' => array(
                'title'=>$this->translator->translate('Install Step 2')
            ),
                'form' => $installForm2));
    }
    public function installstep3Action(){
        if(!setupUtility::checkConfigFile())
            return  $this->redirect()->toRoute('install');
        $installForm3 = new InstallForm3();
        $request = $this->getRequest();
        if($request->isPost()){
            $isSample = $this->params()->fromPost('issample');
            if($isSample == 1){
                $this->flashMessenger()->addSuccessMessage("Data sample inserted");
                setupUtility::insertSampleData();
            }
            $lang = $this->params()->fromPost('lang');
            setupUtility::setLang($lang);
            return $this->redirect()->toRoute('frontend/child',array('controller'=>'login'));
        }
        return  new ViewModel(
            array(  'title' => array(
                'title'=>$this->translator->translate('Install Step 3')
            ),
                'form' =>$installForm3  ));
    }

    public function testAction(){}
}
