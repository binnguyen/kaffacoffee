<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Table;
use Admin\Form\configForm;

use Admin\Model\configModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class ConfigController extends AbstractActionController
{
    protected   $modelConfig;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelConfig = new configModel($doctrine);
        $this->translator =  Utility::translate();

        //check login
        $user = Utility::checkLogin($this);
        if(! is_object($user) && $user == 0){
            $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }else{
            $isPermission = Utility::checkRole($user->userType,ROLE_ADMIN);
            if( $isPermission == false)
                $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }

        return parent::onDispatch($e);
    }
    public function indexAction()
    {
        $combos = $this->modelConfig->findAll();
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelConfig->convertToArray($combos);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Config'),
            'link' => 'admin/config',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'name' => $this->translator->translate('Name'),
                'value' => $this->translator->translate('Value'),

            ),
            'hideDeleteButton' => 1,
            'hideDetailButton' => 1

        );

        return new ViewModel(array('data'=>$data,
            'title'=>$this->translator->translate('Config')));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
        }
        else{
            $config = $this->modelConfig->findOneBy(array('id'=>$id));
            $configForm = new configForm();
            $configForm->setAttribute('action', '/admin/config/add/'.$id);
            $configForm->get('id')->setValue($config->getId());
            $configForm->get('name')->setValue($config->getName());
            $configForm->get('value')->setValue($config->getValue());
            $configForm->get('type')->setValue($config->getType());

            $configType = $config->getType();
            $configValueImg = '';
            if($configType == 'file'){
                $configValueImg = '<img style="width:100px" src="'.$config->getValue().'">';
            }

            $configForm->add(
                array(
                    'type' => $config->getType(),
                    'name' => 'value',
                    'attributes' =>  array(
                        'id' => $config->getName(),
                        'value' => $config->getValue()
                    ),
                    'options' => array(
                        'label' => 'Value',
                    ),
                )
            );

            if($request->isPost()){
                $data = $this->params()->fromPost();
                $value = $config->getValue();
                if($data['type'] == 'file'){
                    $file = $this->params()->fromFiles();
                    $fileName = $file['value']['name'];
                    $fileTmp = $file['value']['tmp_name'];
                    $fileSize = $file['value']['size'];
                    $fileError = $file['value']['error'];
                    $fileType = $file['value']['type'];
                    $filePatch = './public/img/upload/config/';
                    if($fileName != ''){
                       // chmod($filePatch,0777);
                        $SimpleImage = new \SimpleImage();
                        $SimpleImage->load($fileTmp);
                        $SimpleImage->resize(80,60);
                       $move = $SimpleImage->save($filePatch.$fileName);
                     //   $move = move_uploaded_file($fileTmp, "./public/img/upload/config/".$fileName);
//                       if($move){
                           $value = "/img/upload/config/".$fileName;
//                       }else{
//                           $error = error_get_last();
//                           $this->flashMessenger()->addErrorMessage($error);
//                           $this->redirect()->toRoute('admin/child',array('controller'=>'config'));
//                       }

                    }

                }else{
                    $value = $data['value'];
                }
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelConfig->findOneBy(array('id'=>$idFormPost));
                $cat->setValue($value);
                $this->modelConfig->edit($cat);

//                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'config'));
            }
            return new ViewModel(array(
                'data' =>$config,
                'title' => 'Edit Config: '.$config->getName(),
                'form' => $configForm,
                'configValueImg' => $configValueImg
            ));
        }
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


    }

}