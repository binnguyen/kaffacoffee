<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Frontend\Controller;
use Velacolib\Utility\Utility;
use Admin\Entity\OrderDetail;
use Admin\Entity\Orders;
use Admin\Entity\Table;
use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Admin\Model\tableModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Velacolib\Utility\TransactionUtility;


class TableController extends FrontEndController
{
    protected   $modelOrder;
    protected   $modelOrderDetail;
    protected   $tableModel;
    protected   $translator;
    protected  $userLogin;
    public function init(){

        $this->modelOrder = new orderModel($this->doctrineService);
        $this->modelOrderDetail = new orderdetailModel($this->doctrineService);
        $this->tableModel = new tableModel($this->doctrineService);
    }
    public function indexAction()
    {

        $table = $this->tableModel->findBy(array('isdelete'=>0));
        return new ViewModel(
            array(
                'tables'=>$table,
                'title'=>$this->translator->translate('Table'),
            )
        );
    }

    public function addAction()
    {

            $viewData =  Utility::addNewOrder($this->params(),$this->getRequest(),'frontend/child');
            if($viewData['orderId'] != 0){
                $this->redirect()->toRoute('frontend/child',array(
                    'controller'=>'order',
                    'action'=>'detail',
                    'id'=>$viewData['orderId']
                ));
            }


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
            'link' => 'frontend/order',
            'data' =>$dataRow,
            'heading' => array(
//                'id' => 'Id',
                'tableId' => $this->translator->translate('Table'),
                'createDate' => $this->translator->translate('Create date'),
                'totalCost' => $this->translator->translate('Total cost'),
                'coupon' => $this->translator->translate('Coupon code'),
                'couponValue' => $this->translator->translate('Coupon value'),
                'couponDesc' => $this->translator->translate('Coupon description'),
                'surtax' => $this->translator->translate('Surtax'),
                'totalRealCost' => $this->translator->translate('Total real cost (after coupon)'),
            )
        );

        $dataOrderDetail = array(
            'tableTitle'=> $this->translator->translate('Manage order detail'),
            'link' => 'frontend/order',
            'data' =>$this->modelOrderDetail->convertToArray($orderDetails) ,
            'heading' => array(
//                'id' => 'Id',
//                'orderid' => $this->translator->translate('Order Id'),
                'menuid' => $this->translator->translate('Menu'),
                'menucosttype' => $this->translator->translate('Menu cost type'),
                'quantity' => $this->translator->translate('Quantity'),
                'menucost' => $this->translator->translate('Cost'),
                'realcost' => $this->translator->translate('Total real cost')
            ),
            'hideDetailButton' => 1,
            'hideDeleteButton' => 1,
            'hideEditButton' => 1,
        );

        $printButton = array(
            'id'=>$id,
            'windowName'=> $this->translator->translate('Print window') ,
            'height'=>768,
            'width'=>450,
            'ButtonName' => $this->translator->translate('Print')
        );

        return new ViewModel(array(
            'data'=>$dataOrder,
            'dataOrderDetail'=>$dataOrderDetail,
            'printButton'=>$printButton,
        ));

    }


    public function printAction(){
        $this->layout('layout/print');

        $param = $this->params();

        $orderId = $param->fromRoute('id');

        $orderInfo = $this->modelOrder->findBy(array('id'=>$orderId));

        //$dataRow = $this->modelOrder->convertSingleToArray($orderInfo);

        $orderDetails = $this->modelOrderDetail->findBy(array('isdelete'=>0,'orderId'=>$orderId));

       return new ViewModel(array(
           'id'=>$orderId,
           'info'=>$orderInfo,
           'orderDetail'=>$orderDetails
       ));

    }

    public function mailAction(){

        TransactionUtility::checkAndSendNotifyEmail();
        die;

    }



}