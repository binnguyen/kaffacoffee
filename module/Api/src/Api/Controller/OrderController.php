<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Api\Controller;
use Admin\Entity\Menu;
use Admin\Entity\OrderDetail;
use Admin\Entity\Orders;
use Admin\Entity\Table;
use Admin\Model\comboModel;
use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Velacolib\Utility\Utility;
use Admin\Model\menuModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;



class OrderController extends ApiController
{
    protected   $orderModel;
    protected   $modelCombo;
    protected   $orderDetailModel;
    public function init(){
        $this->orderModel = new orderModel($this->doctrineService);
        $this->orderDetailModel = new orderdetailModel($this->doctrineService);
        parent::init();
    }
    public function indexAction()
    {

        //check api
        $userApi = Utility::userApi(
            $this->params()->fromQuery('userName'),
            $this->params()->fromQuery('apiKey')
        );
        if($userApi->getId() == '')
            die(-1);
        $this->userId = $userApi->getId();
        //end check api

        $pageId = $this->params()->fromQuery('page');
        $pageId = isset($pageId)?$pageId:1;

            $orders = $this->orderModel->paginator(
                array('obj.isdelete = 0','obj.userId = '.$this->userId),
                array('orderBy'=>'id',
                    'order'=>'desc'),
                $pageId,
                $this->postPerPage
            );
            $ordersData = $orders['paginator'];
            $pageCount = $orders['pageCount'];
            if($pageId > $pageCount){
                return -1;
            }
            $dataRow = $this->orderModel->convertToArray($ordersData);
            $data =  array(
                'tableTitle'=> $this->translator->translate('Manage menu'),
                'link' => 'api/index',
                'data' =>$dataRow,
                'heading' => array(
                    'id' => 'Id',
                    'tableId' => $this->translator->translate('tableId'),
                    'createDate' => $this->translator->translate('createDate'),
                    'totalCost' => $this->translator->translate('totalCost'),
                    'totalRealCost' => $this->translator->translate('totalRealCost'),
                    'coupon' => $this->translator->translate('coupon'),
                    'couponValue' => $this->translator->translate('couponValue'),
                    'couponDesc' => $this->translator->translate('couponDesc'),
                    'surtax' => $this->translator->translate('surtax'),
                    'userid' => $this->translator->translate('userid'),
                    'status' => $this->translator->translate('status'),
                ),
                'hideDeleteButton' => 1,
                'hideDetailButton' => 0,
                'hideEditButton' => 1,
            );
            return new ViewModel(array('data'=>$data));

    }

    public function addAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            $data = $this->params()->fromPost();
            echo json_encode($data);die;
            //check api
            $userApi = Utility::userApi(
                $data['userName'],
                $data['apiKey']
            );
            if($userApi->getId() == '')
                die(-1);
            $this->userId = $userApi->getId();
            //end check api

            // insert new customer before insert new  order
            $customerId = Utility::createCustomer($data);

                //insert new order
                $order = new Orders();
                $order->setUserId($this->userId);
                $order->setStatus($data['status']);
                $order->setTotalCost($data['total_cost']);
                $order->setTotalRealCost($data['total_real_cost']);
                $order->setCreateDate(time());
                $order->setIsdelete(0);
                $order->setTableId(0);
                $order->setSurtaxId(0);
                $order->setCustomerId($customerId);
                //end insert new order
                $order = $this->orderModel->insert($order);

                $orderId = $order->getId();

                //insert orderDetail
                $orderDetails = $data['detai'];
                foreach($orderDetails as $details){
                    $orderDetail = new OrderDetail();
                    $orderDetail->setOrderId($orderId);
                    $orderDetail->setMenuId($details['menu_id']);
                    $orderDetail->setCostType($details['cost_type']);
                    $orderDetail->setQuantity($details['quantity']);
                    $orderDetail->setMenuCost($details['menu_cost']);
                    $orderDetail->setRealCost($details['real_cost']);
                    $orderDetail->setCustomerId($customerId);
                    $orderDetail->setIsdelete(0);
                    $this->orderDetailModel->insert($orderDetail);
                }
                //insert order detail
                die;
            }
        die;
    }

}