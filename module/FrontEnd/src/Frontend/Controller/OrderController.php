<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Frontend\Controller;

use Admin\Model\transactionModel;
use Velacolib\Utility\Utility;
use Admin\Entity\OrderDetail;
use Admin\Entity\Orders;
use Admin\Entity\Table;
use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Velacolib\Utility\TransactionUtility;
use Zend\Authentication\AuthenticationService;


class OrderController extends AbstractActionController
{
    protected $modelOrder;
    protected $modelOrderDetail;
    protected $modelTransaction;
    protected $translator;
    protected $userLogin;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrineService = $this->sm->get($service_locator_str);
        $this->modelOrder = new orderModel($doctrineService);
        $this->modelOrderDetail = new orderdetailModel($doctrineService);
        $this->modelTransaction = new transactionModel($doctrineService);

        $this->translator = Utility::translate();
        //check login
        $this->userLogin = Utility::checkLogin($this);
        if (!is_object($this->userLogin) && $this->userLogin == 0) {
            $this->redirect()->toRoute('admin/child', array('controller' => 'login'));
        }

        return parent::onDispatch($e);
    }

    public function indexAction()
    {

        $strSQl = 'table.userId = ' . $this->userLogin->userId . ' and
                    table.isdelete = 0
                    and table.createDate >=' . strtotime(date('Y-m-d 0:0:0')) . '
                    and table.createDate <= ' . strtotime(date('Y-m-d 23:59:0'));
        $oders = $this->modelOrder->createQueryFindAll($strSQl);

        $total = $this->modelOrder->sumTotalCostByUserPerDay($strSQl);
//       echo '<pre>';
//        print_r($total);
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelOrder->convertToArray($oders);
        $data = array(
            'tableTitle' => $this->translator->translate('Manage order'),
            'link' => 'frontend/order',
            'data' => $dataRow,
            'heading' => array(
                'id' => $this->translator->translate('Order Id'),
                'tableId' => $this->translator->translate('Table'),
                'userid' => $this->translator->translate('User create'),
                'createDate' => $this->translator->translate('Create date'),
                'totalCost' => $this->translator->translate('Total cost'),
                'coupon' => $this->translator->translate('Coupon'),
                'surtax' => $this->translator->translate('Surtax'),
                'totalRealCost' => $this->translator->translate('Total real cost'),
            ),
            'hideDeleteButton' => 1,
            'hideEditButton' => 1
        );
        return new ViewModel(
            array(
                'data' => $data,
                'title' => $this->translator->translate('Order'),
                'total' => $total
            )
        );
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
            Utility::insertHistory('Delete Order ID:'.$id);
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
                'surtax' => $this->translator->translate('Surtax'),
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
            $this->modelTransaction->deleteAll(array('orderId' => $orderId));
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
                $orderTo->setTotalCost($orderTo->getTotalCost() + $totalCostFrom);
                $orderTo->setTotalRealCost($orderTo->getTotalRealCost() + $totalRealCostFrom);

                $this->modelOrder->edit($orderTo);

                $this->modelOrder->delete(array('id' => $fromTable));


            }
            $this->flashMessenger()->addSuccessMessage('Merge order success!');
            return $this->redirect()->toRoute('frontend/child', array('controller' => 'order', 'action' => 'add'));


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
                            <input type="text" name="data' . $orderDetail->getId() . '[qty]" class="input-small" />
                            </td>
                            <td>
                            <select name="data' . $orderDetail->getId() . '[newCoupon]">
                            <option value="-1">Select...</option>
                            '.$couponHtml.'
                            </select>
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
            $discount = $post['discount'];
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
                $orderEntity->setCouponId($discount);
                $orderEntity->setToTalRealCost(0);
                $orderEntity->setUserId($auth->userId);
                $orderEntity->setSurtaxId(0);
                $orderEntity->setIsdelete(0);
                $orderEntity->setStatus($status);
                $orderLastInsert = $this->modelOrder->insert($orderEntity);
                $lastOrderId = $orderLastInsert->getId();

                $totalPrice = 0;

                $oldPrice = 0;

                $newRealCost = 0;

                foreach ($post as $order) {

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
                            if($order['newCoupon'] != -1){
                                $Cost = $orderDetailModel->getQuantity() * $orderDetailModel->getMenuCost();
                                $orderDetailModel->setDiscount($order['newCoupon']);
                                $orderDetailModel->setRealCost(Utility::getPriceUseCoupon($Cost,$order['newCoupon']));
                            }

                            $this->modelOrderDetail->edit($orderDetailModel);
                            $newCost = $orderDetailModel->getRealCost();


                        } else {

                            // insert new order detail

                            $realCost = $qty * (Utility::getMenuValue($order['menuId'], $order['costType']));

                            $data['menuid'] = $order['menuId'];
                            $data['quantity'] = $qty;
                            $data['menuCost'] = $order['menuCost'];
                            $data['realcost'] = Utility::getPriceUseCoupon($realCost,$order['newCoupon']) ;
                            $data['orderDetailType'] = $order['costType'];
                            $data['discount'] = $order['newCoupon'];
                            $newCost =  $data['realcost'];
                            Utility::insertOrderDetail($data, $lastOrderId);

                            // update old order-item detail

                            $lastQuantity = $order['oldQty'] - $order['qty'];

//                            if ($order['discountType'] != '' && $order['discountType'] == 0) {
//                                $newRealCost = ($lastQuantity * $order['menuCost']) - $order['discountValue'];
//                            } elseif ($order['discountType'] != '' && $order['discountType'] == 1) {
//                                $newRealCost = Utility::roundCost(($lastQuantity * $order['menuCost']) - ((($lastQuantity * $order['menuCost']) * $order['discountValue']) / 100));
//                            } else {
//                                $newRealCost = ($lastQuantity * $order['menuCost']);
//                            }   this code replace by static function Utility::getPriceUseCoupon(price,discountId);

                            $newRealCost = Utility::getPriceUseCoupon($lastQuantity * $order['menuCost'],$order['discountId']) ;


                            $orderDetailModel->setQuantity($lastQuantity);

                            $orderDetailModel->setRealCost($newRealCost);

                            $this->modelOrderDetail->edit($orderDetailModel);

                        }

                    } else {

                          $newRealCost = $orderDetailModel->getRealCost();

                    }

                    $oldPrice += $newRealCost;
                    $totalPrice += $newCost;
                }

                $oldOrderModel =     $this->modelOrder->findOneBy(array(
                    'id'=>$oldOrder
                ));

                $oldOrderModel->setTotalRealCost($oldPrice);

                $oldOrderModel->setTotalCost($oldPrice);

                $this->modelOrder->edit($oldOrderModel);

                $newTotalPrice = $totalPrice;

//                $coupon = Utility::getCouponInfo($discount);
//
//                if ($coupon->getType() == 0) {
//
//                    $realCostAfter = Utility::roundCost($newTotalPrice - $coupon->getValue());
//
//                } elseif ($coupon->getType() == 1) {
//
//                    $realCostAfter = Utility::roundCost($newTotalPrice - ($newTotalPrice * $coupon->getValue()) / 100);
//
//                } else {
//
//                    $realCostAfter = $newTotalPrice;
//
//                } this code replace by function Utility::getPriceUseCoupon(price,discountId);

                $realCostAfter = Utility::getPriceUseCoupon($newTotalPrice,$discount)  ;


                //update last order
                $lastOrder = $this->modelOrder->findOneBy(array(

                    'id' => $lastOrderId

                ));

                $lastOrder->setToTalRealCost($realCostAfter);

                $lastOrder->setTotalCost($newTotalPrice);

                $this->modelOrder->edit($lastOrder);
                // update old order
            }


        }
        $this->flashMessenger()->addSuccessMessage('Split order success!');
        return $this->redirect()->toRoute('frontend/child', array('controller' => 'order', 'action' => 'add'));

    }

}