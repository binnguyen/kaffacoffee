<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Frontend\Controller;
use Admin\Model\transactionModel;
use Velacolib\Utility\Table\AjaxTableSum;
use Velacolib\Utility\Utility;
use Velacolib\Utility\Table;
use Velacolib\Utility\Table\AjaxTable;
use Velacolib\Utility\Table\Detail;

use Admin\Entity\OrderDetail;

use Admin\Entity\Orders;
use Admin\Entity\Managetable;
use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Velacolib\Utility\TransactionUtility;
use Zend\Authentication\AuthenticationService;


class OrderController extends FrontEndController
{
    protected $modelOrder;
    protected $modelOrderDetail;
    protected $modelTransaction;
    protected $translator;
    protected $userLogin;

    public function init()
    {
        parent::init();
        $this->modelOrder = new orderModel($this->doctrineService);
        $this->modelOrderDetail = new orderdetailModel($this->doctrineService);

    }

    public function indexAction()
    {
        $currentUser =   Utility::checkLogin();

        //config table
        /////column for table
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0,'select'=>'id','prefix'=>'o', 'search'=>false, 'type' => 'number' ),
            array('title' =>'Table', 'db' => 'name','dt' => 1,'select'=>'name','prefix'=>'t', 'search'=>true, 'type' => 'text' ,
                'dataSelect'=> Utility::getTableForSelect()
            ),
            array('title' =>'User Name', 'db' => 'userName','dt' => 2,'select'=>'userName','prefix'=>'u', 'search'=>true, 'type' => 'text' ),
            array('title' =>'Create date', 'db' => 'createDate','dt' => 3,'select'=>'createDate','prefix'=>'o', 'search'=>true, 'type' => 'text','formatter'=>function($d,$row){
                return date('d-m-Y h:i:s',$d);
            } ),

            array('title' =>'Coupon', 'db' => 'code','dt' => 4,'select'=>'code','prefix'=>'c', 'search'=>true, 'type' => 'text' ),


            array('title' =>'Total cost', 'db' => 'totalCost','dt' => 5,'select'=>'totalCost','prefix'=>'o', 'search'=>true, 'type' => 'text',
                'formatter' => function($d,$row){
                    return Utility::formatCost($d);
                }
            ),
            array('title' =>'Total real cost', 'db' => 'totalRealCost','select'=>'totalRealCost','prefix'=>'o','dt' => 6, 'search'=>true, 'type' => 'text',
                'formatter' => function($d,$row){
                    return Utility::formatCost($d);
                }
            ),

            array('title' =>'Action', 'db' => 'orderId','dt' => 7, 'select'=>'id','prefix'=>'o', 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/order';
                    return '


                        <a class="btn-xs action action-detail btn btn-default btn-primary " href="'.$actionUrl.'/detail/'.$d.'"><i class="icon-external-link "></i></a>
                    ';
                }
            )

        );
        $d = date('d',time());
        $m = date('m',time());
        $y = date('Y',time());
        $min = strtotime(' 0:0:0 '.$d.'-'.$m.'-'.$y);
        $max = strtotime(' 23:59:59 '.$d.'-'.$m.'-'.$y);
        /////end column for table
        $table = new AjaxTableSum(array(), array(), 'frontend/order');
        $table->setTableColumns($columns);
        $table->setTablePrefix('o');
        $table->setExtendJoin(
            array(
                array("Admin\\Entity\\User", "u", "WITH", "u.id = o.userId"),
                array("Admin\\Entity\\Managetable", "t", "WITH", "t.id = o.tableId"),
                array("Admin\\Entity\\Coupon", "c", "WITH", "c.id = o.couponId"),
            )
        );
        $table->setExtendSQl(
            array(
                array('AND','o.isdelete','=','0'),
                array('AND','o.userId','=',$currentUser->userId),
                array('AND','o.createDate','>=',$min),
                array('AND','o.createDate','<',$max),
            )
        );
        $table->setSumColumn(array('5','6'));
        $table->setAjaxCall('/frontend/order');
        $table->setActionDeleteAll('deleteall');


        $this->tableAjaxRequest($table,$columns,$this->modelOrder);
        //end config table
        return new ViewModel(
            array('table' => $table,
            'title' => $this->translator->translate('Order')));
    }

    public function addAction()
    {

        $viewData = Utility::addNewOrder($this->params(), $this->getRequest(), 'frontend/child');


        return new ViewModel($viewData);


    }

    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->params()->fromPost('id');
            $menu = $this->modelOrder->findOneBy(array('id' => $id));
            $menu->setIsdelete(1);
            $this->modelOrder->edit($menu);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }

    public function detailAction()
    {

        $id = $this->params()->fromRoute('id');
        $orderInfo = $this->modelOrder->findOneBy(array('id' => $id));
        $dataRow = $this->modelOrder->convertSingleToArray($orderInfo);
        $orderDetails = $this->modelOrderDetail->findBy(array('isdelete' => 0, 'orderId' => $id));

        $dataOrder = array(
            'title' => $this->translator->translate('Detail') . ': #' . $orderInfo->getId(),
            'link' => 'frontend/order',
            'data' => $dataRow,
            'heading' => array(
//                'id' => 'Id',
                'tableId' => $this->translator->translate('Table'),
                'createDate' => $this->translator->translate('Create date'),
                'totalCost' => $this->translator->translate('Total cost'),
                'coupon' => $this->translator->translate('Coupon code'),
                'couponValue' => $this->translator->translate('Coupon value'),
                'couponDesc' => $this->translator->translate('Coupon description'),
                'totalRealCost' => $this->translator->translate('Total real cost (after coupon)'),
            )
        );

        $dataOrderDetail = array(
            'tableTitle' => $this->translator->translate('Manage order detail'),
            'link' => 'frontend/order',
            'data' => $this->modelOrderDetail->convertToArray($orderDetails),
            'heading' => array(
//                'id' => 'Id',
//                'orderid' => $this->translator->translate('Order Id'),
                'menuid' => $this->translator->translate('Menu'),
                'menucosttype' => $this->translator->translate('Menu cost type'),
                'quantity' => $this->translator->translate('Quantity'),
                'menucost' => $this->translator->translate('Cost'),
                'discount' => $this->translator->translate('Discount'),
                'realcost' => $this->translator->translate('Total real cost')
            ),
            'hideDetailButton' => 1,
            'hideDeleteButton' => 1,
            'hideEditButton' => 1,
        );

        $printButton = array(
            'id' => $id,
            'windowName' => $this->translator->translate('Print window'),
            'height' => 768,
            'width' => 450,
            'ButtonName' => $this->translator->translate('Print')
        );

        return new ViewModel(array(
            'data' => $dataOrder,
            'dataOrderDetail' => $dataOrderDetail,
            'printButton' => $printButton,
        ));

    }

    public function printAction()
    {
        $this->layout('layout/print');

        $param = $this->params();

        $orderId = $param->fromRoute('id');

        $orderInfo = $this->modelOrder->findOneBy(array('id' => $orderId));

        //$dataRow = $this->modelOrder->convertSingleToArray($orderInfo);

        $orderDetails = $this->modelOrderDetail->findBy(array('isdelete' => 0, 'orderId' => $orderId));

        return new ViewModel(array(
            'id' => $orderId,
            'info' => $orderInfo,
            'orderDetail' => $orderDetails
        ));

    }

    public function mailAction()
    {

        TransactionUtility::checkAndSendNotifyEmail();
        die;

    }

    public function switchAction()
    {

        $request = $this->getRequest();
        if ($request->isPost()) {
            $orderId = $this->params()->fromPost('order_id_hidden');
            $userId = $this->params()->fromPost('user_id');

            $orderInfo = $this->modelOrder->findOneBy(array('id' => $orderId));

            $orderInfo->setUserId($userId);
            $this->modelOrder->edit($orderInfo);
        }
        $url = $_SERVER['HTTP_REFERER'];
        header("Location:" . $url);
        die;
    }

    public function ajaxDeleteOrderAction()
    {
        if ($this->getRequest()->isPost()) {
            $orderId = $this->params()->fromPost('orderId');
            $orderModel = $this->modelOrder;
            $orderModel->delete(array('id' => $orderId));
            $this->modelOrderDetail->deleteAll(array('orderId' => $orderId));
            echo 1;
            die;
        }
        echo 0;
        die;

    }

    public function mergeAction()
    {

        if ($this->getRequest()->isPost()) {


            $fromTable = $this->params()->fromPost('fromTable');
            $toOrderId = $this->params()->fromPost('toTable');

            $orderDetailModel = $this->modelOrderDetail->findBy(array(
                'orderId' => $fromTable
            ));


            if (!empty($orderDetailModel) && $fromTable != $toOrderId) {

                foreach ($orderDetailModel as $orderDetail) {
                    $orderDetail->setOrderId($toOrderId);
                    $this->modelOrderDetail->edit($orderDetail);
                }

                $orderFrom = $this->modelOrder->findOneBy(array(
                    'id' => $fromTable
                ));

                $totalCostFrom = $orderFrom->getTotalCost();

                $totalRealCostFrom = $orderFrom->getTotalRealCost();

                $orderTo = $this->modelOrder->findOneBy(array('id' => $toOrderId));

                $finalTotalRealCost = $orderTo->getTotalCost() + $totalCostFrom;

                $orderTo->setTotalCost($finalTotalRealCost);


                $finalTotalRealCost = Utility::getPriceUseCoupon($finalTotalRealCost,$orderTo->getCouponId());

                $finalTotalRealCost = Utility::getPriceUseSurtax($finalTotalRealCost,$orderTo->getSurtaxId());



                $orderTo->setTotalRealCost($finalTotalRealCost);

                $this->modelOrder->edit($orderTo);

                $this->modelOrder->delete(array('id' => $fromTable));
                $this->flashMessenger()->addSuccessMessage('Merge order success!');
                return $this->redirect()->toRoute('frontend/child', array('controller' => 'order', 'action' => 'add'));

            }else{
                $this->flashMessenger()->addErrorMessage('Opp! Something Wrong, pls try again...');
                return $this->redirect()->toRoute('frontend/child', array('controller' => 'order', 'action' => 'add'));
            }




        }

    }

    public function ajaxDetailOrderAction()
    {
        $response = array();
        $coupons = Utility::getCouponCheckExpire();
        $couponHtml = '';
        foreach($coupons as $coupon){
            $couponHtml.='<option value="'.$coupon->getId().'">'.$coupon->getDescription().'</option>';
        }
        if ($this->getRequest()->isXmlHttpRequest()) {

            $orderId = $this->params()->fromPost('order_id');
            $orderDetailModel = $this->modelOrderDetail->findBy(array(
                'orderId' => $orderId
            ));
            $html = '';
            $response['data'] = '';
            if (!empty($orderDetailModel)) {

                foreach ($orderDetailModel as $orderDetail) {
                    $menu = Utility::getMenuInfo($orderDetail->getMenuId());
                    $counponInfo = Utility::getCouponInfo($orderDetail->getDiscount());
                    $html .= '<tr>
                            <td >
                            ' . $menu->getName() . '
                            <input type="hidden" name="data' . $orderDetail->getId() . '[orderDetailId]" value="' . $orderDetail->getId() . '" />
                            </td>
                            <td>' . $orderDetail->getRealCost() . '</td>
                            <td>
                            ' . $orderDetail->getQuantity() . '
                            <input type="hidden" name="data' . $orderDetail->getId() . '[menuCost]" value="' . $orderDetail->getMenuCost() . '" />
                             <input type="hidden" name="data' . $orderDetail->getId() . '[menuId]" value="' . $orderDetail->getMenuId() . '" />
                             <input type="hidden" name="data' . $orderDetail->getId() . '[costType]" value="' . $orderDetail->getCostType() . '" />
                            <input type="hidden" name="data' . $orderDetail->getId() . '[oldQty]" value="' . $orderDetail->getQuantity() . '" />
                            <input type="hidden" name="data' . $orderDetail->getId() . '[discountValue]" value="' . $counponInfo->getValue() . '" />
                            <input type="hidden" name="data' . $orderDetail->getId() . '[discountId]" value="' . $counponInfo->getId() . '" />
                             <input type="hidden" name="data' . $orderDetail->getId() . '[discountType]" value="' . $counponInfo->getType() . '" />

                            </td>
                            <td>
                            <input type="number" name="data' . $orderDetail->getId() . '[qty]" class="input-small disable-alphabet" value="0" />
                            </td>


                    </tr>';
                }

                $response['data'] = $html;
                $response['oldOrderId'] =  $orderId;

            }
            echo json_encode($response);
            die;


        }


    }

    function splitAction()
    {

        if ($this->getRequest()->isPost()) {

            $post = $this->params()->fromPost();

            $table = $post['table-new'];
            $discount = -1;
        //    $oldDiscount = $post['discount'];
            $oldOrder = $post['oldOrder'];
            unset($post['table-new']);
            unset($post['discount']);
            unset($post['oldOrder']);
            if (!empty($post)) {
                $Auth_service = new AuthenticationService();
                $auth = $Auth_service->getIdentity();
                $status = 'pending';
                $orderEntity = new Orders();
                $orderEntity->setTotalCost(0);
                $orderEntity->setTableId($table);
                $orderEntity->setCreateDate(time());
                $orderEntity->setCouponId(-1);
                $orderEntity->setToTalRealCost(0);
                $orderEntity->setUserId($auth->userId);
                $orderEntity->setSurtaxId(0);
                $orderEntity->setIsdelete(0);
                $orderEntity->setStatus($status);
                $orderEntity->setNewDate(date('Y-m-d H:i:s',time()));
                $orderLastInsert = $this->modelOrder->insert($orderEntity);
                $lastOrderId = $orderLastInsert->getId();

                $totalPrice = 0;

                $oldPrice = 0;

                $newRealCost = 0;
//                echo '<pre>';
//                print_r($post);die;
                foreach ($post as $order) {

                    $order['newCoupon'] = -1;

                    $orderDetailId = $order['orderDetailId'];

                    $oldQty = $order['oldQty'];

                    $qty = $order['qty'];

                    $totalRealCost = $order['menuCost'] * $order['oldQty'];


                    ($qty >= $oldQty) ? $qty = $oldQty : $qty = $qty;



                    $orderDetailModel = $this->modelOrderDetail->findOneBy(array('id' => $orderDetailId));
                    // check quantity split every order

                    $orderDetail = new OrderDetail();

                    if ($qty > 0) {

                        if ($qty >= $oldQty) {

                            // update orderId for order detail
                            $orderDetailModel->setOrderId($lastOrderId);
                          //  if($order['newCoupon'] != -1){
                                $Cost = $orderDetailModel->getQuantity() * $orderDetailModel->getMenuCost();
//                                $orderDetailModel->setDiscount($order['newCoupon']);
//                                $orderDetailModel->setRealCost(Utility::getPriceUseCoupon($Cost,$order['newCoupon']));

                                $orderDetailModel->setDiscount(-1);
                                $orderDetailModel->setRealCost($Cost);
                        //    }

                            $this->modelOrderDetail->edit($orderDetailModel);
                            $newCost = $orderDetailModel->getRealCost();


                        } else {

                            // insert new order detail

                            $realCost = $qty * (Utility::getMenuValue($order['menuId'], $order['costType']));

                            $data['menuid'] = $order['menuId'];
                            $data['quantity'] = $qty;
                            $data['menuCost'] = $order['menuCost'];
//                            $data['realcost'] = Utility::getPriceUseCoupon($realCost,$order['newCoupon']) ;
                            $data['realcost'] = $realCost ;
                            $data['orderDetailType'] = $order['costType'];
//                            $data['discount'] = $order['newCoupon'];
                            $data['discount'] = -1;
                            $newCost =  $data['realcost'];
                            Utility::insertOrderDetail($data, $lastOrderId);

                            // update old order-item detail

                            $lastQuantity = $order['oldQty'] - $order['qty'];


                            $newRealCost = $lastQuantity * $order['menuCost'] ;


                            $orderDetailModel->setQuantity($newRealCost);

                            $orderDetailModel->setRealCost($newRealCost);

                            $this->modelOrderDetail->edit($orderDetailModel);

                        }

                    } else {

//                        $newRealCost = $orderDetailModel->getRealCost();

                    }

                }

                $oldOrderModel =     $this->modelOrder->findOneBy(array(
                    'id'=>$oldOrder
                ));

                $sumCost = $this->modelOrderDetail->createQuerySumOrderDetail($oldOrder);


                $returnCost = $sumCost[0]['realCost'];

                $oldTotalCost = $returnCost;

//                $oldPrice = Utility::getPriceUseCoupon($oldTotalCost,$oldOrderModel->getCouponId());
//                $oldPrice = Utility::getPriceUseSurtax($oldPrice,$oldOrderModel->getSurtaxId());



                $oldOrderModel->setTotalRealCost($oldTotalCost);

                $oldOrderModel->setTotalCost($oldTotalCost);

                $this->modelOrder->edit($oldOrderModel);

                $newTotalPrice = $totalPrice;


                $sumCostLast = $this->modelOrderDetail->createQuerySumOrderDetail($lastOrderId);

                $returnCostLast = $sumCostLast[0]['realCost'];


//                $realCostAfter = Utility::getPriceUseCoupon($returnCostLast,$discount)  ;
                $realCostAfter = $returnCostLast ;


                //update last order
                $lastOrder = $this->modelOrder->findOneBy(array(

                    'id' => $lastOrderId

                ));

                $lastOrder->setToTalRealCost($realCostAfter);

                $lastOrder->setTotalCost($realCostAfter);

                $this->modelOrder->edit($lastOrder);
                // update old order
            }


        }
        $this->flashMessenger()->addSuccessMessage('Split order success!');
        return $this->redirect()->toRoute('frontend/child', array('controller' => 'order', 'action' => 'add'));

    }

}