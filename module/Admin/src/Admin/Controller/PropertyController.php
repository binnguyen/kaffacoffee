<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Property;
use Admin\Entity\Table;
use Admin\Form\propertyForm;

use Admin\Model\propertyModel;
use Velacolib\Utility\Table\AjaxTable;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;



class PropertyController extends AdminGlobalController
{
    protected   $modelProperty;
    protected  $translator;
    public function init(){

        $this->modelProperty = new propertyModel($this->doctrineService);
    }



    public function indexAction()
    {
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name', 'dt' => 1, 'search'=>false, 'type' => 'text' ),
            array('title' =>'Quantity', 'db' => 'quantity', 'dt' => 1, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Unit', 'db' => 'unit', 'dt' => 1, 'search'=>false, 'type' => 'text' ),
            array('title' =>'Description', 'db' => 'des', 'dt' => 1, 'search'=>false, 'type' => 'text' ),
        );

        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/property');
        $table->setTablePrefix('ts');
        $table->setAjaxCall('/admin/property');
        $this->tableAjaxRequest($table,$columns,$this->modelProperty);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('User History')));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $configForm = new propertyForm();
            $configForm->setAttribute('action', '/admin/property/add');

            if($request->isPost()){
                $data = $this->params()->fromPost();
                $cat = new Property();
                $cat->setName($data['name']);
                $cat->setQuantity($data['quantity']);
                $cat->setUnit($data['unit']);
                $cat->setDes($data['des']);
                $this->modelProperty->edit($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'property'));
            }

            return new ViewModel(array(
                'form'=>$configForm ,
                'title'=>$this->translator->translate('Property'),

            ));

        }
        else{
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelProperty->findOneBy(array('id'=>$idFormPost));
                $cat->setName($data['name']);
                $cat->setQuantity($data['quantity']);
                $cat->setUnit($data['unit']);
                $cat->setDes($data['des']);
                $this->modelProperty->edit($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'property'));
            }
            $config = $this->modelProperty->findOneBy(array('id'=>$id));
            $configForm = new propertyForm();
            $configForm->setAttribute('action', '/admin/property/add/'.$id);
            $configForm->get('id')->setValue($config->getId());
            $configForm->get('name')->setValue($config->getName());
            $configForm->get('quantity')->setValue($config->getQuantity());
            $configForm->get('unit')->setValue($config->getUnit());
            $configForm->get('des')->setValue($config->getDes());
            return new ViewModel(array(
                'data' =>$config,
                'title' => $this->translator->translate('Edit').' '.$config->getName(),
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