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

use Admin\Entity\Supplier;
use Admin\Entity\SupplierFor;
use Admin\Entity\SupplierItem;
use Admin\Entity\Managetable;

use Admin\Form\eventForm;
use Admin\Form\supplierForm;
use Admin\Model\supplierModel;
use Admin\Model\supplyForModel;
use Admin\Model\supplyItemModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class SupController extends AdminGlobalController
{
    protected   $modelSupplier;
    protected   $modelSupplyFor;
    protected  $translator;


    public function init(){
        parent::init();
        $this->modelSupplier = new supplierModel($this->doctrineService);
        $this->modelSupplyFor = new supplyForModel($this->doctrineService);
    }


    public function indexAction()
    {

        $columns = array(

            array('title' =>'ID', 'db' => 'id', 'dt' => 0,'search'=>false, 'type' => 'number' ),
            array('title' =>'Contact Name', 'db' => 'contact','dt' => 1, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Compay Name', 'db' => 'company','dt' => 2, 'search'=>true, 'type' => 'number','formatter'=>function($d,$row){
              return $d;
            }),
            array('title' =>'Phone', 'db' => 'phone','dt' => 3, 'search'=>true, 'type' => 'number'            ),
            array('title' =>'Mobile', 'db' => 'mobile','dt' => 4, 'search'=>true, 'type' => 'number'                 ),

            array('title' =>'Address', 'db' => 'addr','dt' => 5,'search'=>false, 'type' => 'text' ),

            array('title' =>'Email', 'db' => 'email','dt' => 6, 'search'=>true, 'type' => 'text'),

            array('title' =>'Action','db'=>'id','dt' => 7 , 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/supplier';
                    return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';

                }
            ),


        );


        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/supplier');
        $table->setTablePrefix('m');
        $table->setExtendSQl(array(
            array('AND','m.isdelete','=','0'),
        ));

        $table->setAjaxCall('/admin/supplier');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelSupplier);
        //end config table


        return new ViewModel(array(
            'table' => $table,
            'title' => $this->translator->translate('Supplier')));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $event = new Supplier();
            $configForm = new supplierForm();
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $event->setCompanyName($data['company']);
                $event->setContactName($data['name']);
                $event->setAddr($data['addr']);
                $event->setPhone($data['phone']);
                $event->setMobile($data['mobile']);
                $event->setEmail($data['email']);
                $event->setSuplierFor(0);
                $event->setIsdelete(0);
                $inserted= $this->modelSupplier->insert($event);

                $dataSuplyFor = $data['supply_for'];
                foreach($dataSuplyFor as $item){
                    $this->insertSupplyItem($item,$inserted->getId());
                }

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'supplier'));
            }

            return new ViewModel(array(
                'data' =>$event,
                'title' => $this->translator->translate('Add New Supplier'),
                'form' => $configForm
            ));
        }
        else{
            $event = $this->modelSupplier->findOneBy(array('id'=>$id));
            $configForm = new supplierForm();
            $configForm->setAttribute('action', '/admin/supplier/add/'.$id);
            $arraySupplyfor = Utility::getSupplyItemOfSupplier($id);



            $configForm->get('id')->setValue($event->getId());
            $configForm->get('company')->setValue($event->getCompanyName());
            $configForm->get('name')->setValue($event->getContactName());
            $configForm->get('phone')->setValue($event->getPhone());
            $configForm->get('mobile')->setValue($event->getMobile());
            $configForm->get('email')->setValue($event->getEmail());
            $configForm->get('addr')->setValue($event->getAddr());
            $configForm->get('supply_for')->setValue($this->parseToArraySelect($arraySupplyfor));



            if($request->isPost()){
                $data = $this->params()->fromPost();
                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelSupplier->findOneBy(array('id'=>$idFormPost));
                $event->setCompanyName($data['company']);
                $event->setContactName($data['name']);
                $event->setAddr($data['addr']);
                $event->setPhone($data['phone']);
                $event->setMobile($data['mobile']);
                $event->setEmail($data['email']);
                $event->setSuplierFor(0);
                $event->setIsdelete(0);
                $this->modelSupplier->edit($event);

                //update supply item
                $this->modelSupplyFor->deleteAll(array('suppilerId'=>$idFormPost));
                $dataSuplyFor = $data['supply_for'];
                foreach($dataSuplyFor as $item){
                    $this->insertSupplyItem($item,$idFormPost);
                }
                $arraySupplyfor = Utility::getSupplyItemOfSupplier($id);
                //update form
                $configForm->get('company')->setValue($event->getCompanyName());
                $configForm->get('name')->setValue($event->getContactName());
                $configForm->get('phone')->setValue($event->getPhone());
                $configForm->get('mobile')->setValue($event->getMobile());
                $configForm->get('email')->setValue($event->getEmail());
                $configForm->get('addr')->setValue($event->getAddr());
                $configForm->get('supply_for')->setValue($this->parseToArraySelect($arraySupplyfor));

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'supplier'));
            }
            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit event: '.$event->getCompanyName(),
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
            $event = $this->modelSupplier->findOneBy(array('id'=>$id));
            $event->setIsdelete(1);
            $this->modelSupplier->edit($event);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {


    }
    private function insertSupplyItem($suplyForItemID,$supplierID){
        $suplyForItem = new SupplierFor();
        $suplyForItem->setSuppilerId($supplierID);
        $suplyForItem->setSupplierItem($suplyForItemID);
        $this->modelSupplyFor->insert($suplyForItem);
    }


    private function parseToArraySelect($data){
        $array = array();
        foreach($data as $item){
            $array[$item['id']] = $item['id'];
        }
        return $array;
    }

    public function getsuplierAction(){

        $suplierItemId = $this->params()->fromPost('suplier_item_id');
         $supplier  = array();
        $suplierFor = $this->modelSupplyFor->findBy(array('supplierItem'=>$suplierItemId));
        $response = array();
         if(! empty($suplierFor)){
             foreach($suplierFor as $suplierForItem){
                 $suppli = Utility::getSupplierInfo($suplierForItem->getSuppilerId());
                 if($suppli->getIsdelete() == 0){
                    $supplier[$suppli->getId()] = $suppli->getCompanyName() ;
                 }
             }
             $response['status'] = true;
             $response['result'] =   ($supplier);

         } else{
             $response['status'] = false;
         }
        echo json_encode($response);
        die;



    }
}