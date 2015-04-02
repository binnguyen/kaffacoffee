<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/4/14
 * Time: 9:43 AM
 */

namespace Admin\Controller;
use Admin\Entity\TrackingTore;
use Admin\Form\trackingToreForm;
use Admin\Model\trackingToreModel;
use Zend\View\Model\ViewModel;
use Velacolib\Utility\Table\AjaxTable;
use Zend\Mvc\Controller\AbstractActionController;



class TrackingToreController extends AdminGlobalController {


    protected   $modelTracking;
    protected  $translator;
    public function init(){
        $this->modelTracking = new trackingToreModel($this->doctrineService);
    }
    public function indexAction(){
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name', 'dt' => 1, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Item Id', 'db' => 'supplierItemId', 'dt' => 2, 'search'=>false, 'type' => 'number' ),

            array('title' =>'Item Name', 'db' => 'supplierItemName', 'dt' => 4, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Quantity', 'db' => 'quantity', 'dt' => 3, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Note', 'db' => 'note', 'dt' => 5, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Time', 'db' => 'time', 'dt' => 6, 'search'=>false, 'type' => 'number',
                 'formatter'  => function($d,$row){
                     return date('d-m-Y h:i:s',$d);
                 }
            ),

        );

        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/tracking-tore');
        $table->setTablePrefix('ts');
        $table->setAjaxCall('/admin/tracking-tore');
        $this->tableAjaxRequest($table,$columns,$this->modelTracking);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Manage Tracking')));
    }

    public function addAction(){
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $configForm = new trackingToreForm();
            $configForm->setAttribute('action', '/admin/tracking-tore/add');

            if($request->isPost()) {
                $data = $this->params()->fromPost();
                $tracking = new TrackingTore();
                $tracking->setName($data['name']);
                $tracking->setQuantity($data['quantity']);
                $tracking->setSupplierItemId($data['supplierItemId']);
                $tracking->setSupplierItemName($data['supplierItemName']);
                $tracking->setNote($data['note']);
                $tracking->setTime(date('Y-m-d',strtotime($data['time'])));
                $this->modelTracking->insert($tracking);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert tracking success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'tracking-tore'));
            }
            //insert new user

            return new ViewModel(array(
                'title'=> $this->translator->translate('Add new tracking'),
                'form'=>$configForm
            ));
        }
        else{

            $surtax = $this->modelTracking->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $idFormPost = $data['id'];
                $tracking = $this->modelTracking->findOneBy(array('id'=>$idFormPost));
                $tracking->setName($data['name']);
                $tracking->setQuantity($data['quantity']);
                $tracking->setSupplierItemId($data['supplierItemId']);
                $tracking->setSupplierItemName($data['supplierItemName']);
                $tracking->setNote($data['note']);
                $tracking->setTime(date('Y-m-d H:i:s',$data['time']));
                $this->modelTracking->edit($tracking);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'tracking-tore'));
            }
            $config = $this->modelTracking->findOneBy(array('id'=>$id));
            $configForm = new trackingToreForm();
            $configForm->setAttribute('action', '/admin/tracking-tore/add/'.$id);
            $configForm->get('id')->setValue($config->getId());
            $configForm->get('name')->setValue($config->getName());
            $configForm->get('quantity')->setValue($config->getQuantity());
            $configForm->get('supplierItemId')->setValue($config->getSupplierItemId());
            $configForm->get('supplierItemName')->setValue($config->getSupplierItemName());
            $configForm->get('note')->setValue($config->getNote());
            $configForm->get('time')->setValue($config->getTime());

            return new ViewModel(array(
                'data' =>$surtax,
                'title' => $this->translator->translate('Edit tracking:') ,
                'form'  =>$configForm
            ));

        }
    }

    public function detail(){

    }

    public function deleteAction()
    {
        //get user by id
            $id = $this->params()->fromRoute('id');
          //  $menu = $this->modelTracking->findOneBy(array('id' => $id));
           // $menu->setIsdelete(1);
          //  $this->modelTracking->edit($menu);
            $this->modelTracking->delete(array('id'=>$id));
            $this->flashMessenger()->addSuccessMessage('Delete success');
            $this->redirect()->toRoute('admin/child',array(
                'controller'=>'tracking-tore',
                ));

    }
} 