<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/28/14
 * Time: 10:04 AM
 */

namespace Admin\Controller;


use Admin\Model\supplyForModel;
use Zend\Mvc\Controller\AbstractActionController;

use Admin\Entity\SupplierItem;
use Admin\Entity\Managetable;
use Admin\Form\supplieritemForm;

use Velacolib\Utility\Table;
use Velacolib\Utility\Table\AjaxTable;
use Velacolib\Utility\Table\Detail;


use Admin\Model\supplyItemModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class SupplieritemController  extends AdminGlobalController {

    protected   $modelSubItem;
    protected   $modelSubItemFor;
    protected  $translator;



    public function init(){
        parent::init();
        $this->modelSubItem = new supplyItemModel($this->doctrineService);
        $this->modelSubItemFor = new supplyForModel($this->doctrineService);
    }




    public function indexAction()
    {

        $columns = array(

            array('title' =>'ID', 'db' => 'id', 'dt' => 0,'search'=>false, 'type' => 'number' ),
            array('title' =>'Value', 'db' => 'value','dt' => 1, 'search'=>true, 'type' => 'text' ),

            array('title' =>'Action','db'=>'id','dt' => 2 , 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/supplieritem';
                    return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';

                }
            ),


        );


        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/supplieritem');
        $table->setTablePrefix('m');
        $table->setExtendSQl(array(
            array('AND','m.isdelete','=','0'),
        ));

        $table->setAjaxCall('/admin/supplieritem');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelSubItem);
        //end config table


        return new ViewModel(array(
            'table' => $table,
            'title' => $this->translator->translate('Supplier Item')));
    }


    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $event = new SupplierItem();
            $configForm = new supplieritemForm();
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $event->setValue($data['value']);
                $event->setIsdelete(0);
                $inserted= $this->modelSubItem->insert($event);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'supplieritem'));

            }

            return new ViewModel(array(
                'data' =>$event,
                'title' => $this->translator->translate('Add new supplier item'),
                'form' => $configForm
            ));
        }
        else{
            $event = $this->modelSubItem->findOneBy(array('id'=>$id));

            $configForm = new supplieritemForm();
            $configForm->setAttribute('action', '/admin/supplieritem/add/'.$id);

            $configForm->get('id')->setValue($event->getId());
            $configForm->get('value')->setValue($event->getValue());

            if($request->isPost()){
                $data = $this->params()->fromPost();

                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelSubItem->findOneBy(array('id'=>$idFormPost));
                $event->setValue($data['value']);
                $event->setIsdelete(0);
                $this->modelSubItem->edit($event);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'supplieritem'));

            }
            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit event: '.$event->getValue(),
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
            $event = $this->modelSubItem->findOneBy(array('id'=>$id));

            $event->setIsdelete(1);
            $this->modelSubItem->edit($event);
            $this->modelSubItemFor->deleteAll(array('supplierItem'=>$id));
            echo 1;
        }
        die;

    }

    public function detailAction(){
        $id = $this->params()->fromRoute('id');
        $orderInfo = $this->modelSubItem->findOneBy(array('id'=>$id));
        $dataRow = $this->modelSubItem->convertSingleToArray($orderInfo);
        $orderDetails = $this->modelSubItem->findBy(array('isdelete'=>0,'id'=>$id));
        $dataOrder =  array(
            'title'=> $this->translator->translate('Detail').': #'.$orderInfo->getId(),
            'link' => 'admin/supplieritem',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'value' => $this->translator->translate('Value'),

            )
        );

        return new ViewModel(array('data'=>$dataOrder));
    }

    private function parseToArraySelect($data){
        $array = array();
        foreach($data as $item){
            $array[$item['id']] = $item['id'];
        }
        return $array;
    }


} 