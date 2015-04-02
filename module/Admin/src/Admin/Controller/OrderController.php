<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;

use Velacolib\Utility\Utility;
use Admin\Entity\OrderDetail;
use Admin\Entity\Orders;
use Admin\Entity\Managetable;

use Velacolib\Utility\Table;
use Velacolib\Utility\Table\AjaxTable;
use Velacolib\Utility\Table\Detail;


use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class OrderController extends AdminGlobalController
{
    protected   $modelOrder;
    protected   $modelOrderDetail;
    protected   $translator;


      public function init(){
          $this->modelOrder = new orderModel($this->doctrineService);
          $this->modelOrderDetail = new orderdetailModel($this->doctrineService);
      }


    public function indexAction()
    {
        //config table
        /////column for table
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0,'select'=>'id','prefix'=>'o', 'search'=>false, 'type' => 'number' ),
            array('title' =>'Table', 'db' => 'name','dt' => 1,'select'=>'name','prefix'=>'t', 'search'=>true, 'type' => 'text' ),
            array('title' =>'User Name', 'db' => 'userName','dt' => 2,'select'=>'userName','prefix'=>'u', 'search'=>true, 'type' => 'text' ),
            array('title' =>'Create date', 'db' => 'createDate','dt' => 3,'select'=>'createDate','prefix'=>'o', 'search'=>true, 'type' => 'text','formatter'=>function($d,$row){
                return date('d-m-Y h:i:s',$d);
            } ),
            array('title' =>'Total cost', 'db' => 'totalCost','dt' => 4,'select'=>'totalCost','prefix'=>'o', 'search'=>true, 'type' => 'text',
             'formatter' => function($d,$row){
        return Utility::formatCost($d);
    }
            ),
            array('title' =>'Total real cost', 'db' => 'totalRealCost','select'=>'totalRealCost','prefix'=>'o','dt' => 5, 'search'=>true, 'type' => 'text' ,
            'formatter' => function($d,$row){
                    return Utility::formatCost($d);
              }
            ),
            array('title' =>'Coupon', 'db' => 'code','dt' => 6,'select'=>'code','prefix'=>'c', 'search'=>true, 'type' => 'text' ),
            array('title' =>'Action', 'db' => 'orderId','dt' => 7, 'select'=>'id','prefix'=>'o', 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/order';
                    return '
                        <a class="btn-xs action action-detail btn btn-info btn-default" href="'.$actionUrl.'/detail/'.$d.'"><i class="icon-info"></i></a>
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';
                }
            )

        );
        /////end column for table
        $table = new AjaxTable(array(), array(), 'admin/order');
        $table->setTableColumns($columns);
        $table->setTablePrefix('o');
        $table->setExtendJoin(
            array(
                array(" Admin\\Entity\\User", "u", "WITH", "u.id = o.userId "),
                array(" Admin\\Entity\\Managetable", "t", "WITH", "t.id = o.tableId "),
                array(" Admin\\Entity\\Coupon", "c", "WITH", "c.id = o.couponId "),
            )
        );
        $table->setExtendSQl(array(
            array('AND','o.isdelete','=','0'),
        ));
        $table->setAjaxCall('/admin/order');
        $table->setActionDeleteAll('deleteall');


        $this->tableAjaxRequest($table,$columns,$this->modelOrder);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Manage Order')));
    }

    public function addAction()
    {

        $viewData =  Utility::addNewOrder($this->params(),$this->getRequest());

        return new ViewModel($viewData);

    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $menu = $this->modelOrder->findOneBy(array('id'=>$id));
            $menu->setIsdelete(1);
            $this->modelOrder->edit($menu);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function detailAction(){
        $id = $this->params()->fromRoute('id');
        $orderInfo = $this->modelOrder->findOneBy(array('id'=>$id));
        $dataRow = $this->modelOrder->convertSingleToArray($orderInfo);
        $orderDetails = $this->modelOrderDetail->findBy(array('isdelete'=>0,'orderId'=>$id));
        $dataOrder =  array(
            'title'=> $this->translator->translate('Detail').': #'.$orderInfo->getId(),
            'link' => 'admin/order',
            'data' =>$dataRow,
            'heading' => array(
//                'id' => 'Id',
                'tableId' => $this->translator->translate('Table'),
                'createDate' => $this->translator->translate('Create date'),
                'coupon' => $this->translator->translate('Coupon code'),
                'couponValue' => $this->translator->translate('Coupon value'),
                'couponDesc' => $this->translator->translate('Coupon description'),
                'totalCost' => $this->translator->translate('Total cost'),
                'totalRealCost' => $this->translator->translate('Total Real Cost (after coupon)'),
            )
        );
        $dataOrderDetail = array(
            'tableTitle'=>$this->translator->translate('Manage order detail'),
            'link' => 'admin/order',
            'data' =>$this->modelOrderDetail->convertToArray($orderDetails) ,
            'heading' => array(
                'id' => 'Id',
//                'orderid' => $this->translator->translate('Order id'),
                'menuid' => $this->translator->translate('Menu'),
                'menucosttype' => $this->translator->translate('Menu cost type'),
                'quantity' => $this->translator->translate('Quantity'),
                'menucost' => $this->translator->translate('Cost'),
                'realcost' => $this->translator->translate('Total real cost')
            ),
            'hideEditButton' => 1,
            'hideDeleteButton' => 1,
            'hideDetailButton' => 1
        );
        $printButton = array(
            'id'=>$id,
            'windowName'=> $this->translator->translate('Print window') ,
            'height'=>768,
            'width'=>450,
            'ButtonName' => $this->translator->translate('Print')
        );
//        $dataOrderDetail =  array(
//            'title'=> 'Detail: #'.$orderInfo->getId(),
//            'link' => 'admin/order',
//            'data' =>$orderDetails,
//            'heading' => array(
//                'id' => 'Id',
//                'orderid' => 'Order Id',
//                'menuid' => 'Menu',
//                'quantity' => 'Quantity',
//                'menucost' => 'Menu Cost',
//                'realcost' => 'Real Cost',
//            )
//        );
        return new ViewModel(array(
            'data'=>$dataOrder,
            'dataOrderDetail'=>$dataOrderDetail  ,
            'printButton'=>$printButton,
        ));
    }
    public  function statistic(){

        if($this->getRequest()->isPost()){

        }



    }
    public function ajaxDeleteOrderAction(){
        if($this->getRequest()->isPost()){
            $orderId = $this->params()->fromPost('orderId');
            $orderModel = $this->modelOrder;
            $orderModel->delete(array('id'=>$orderId));
            echo 1;
            die;
        }
        echo 0;
        die;

    }

}