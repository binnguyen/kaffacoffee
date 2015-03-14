<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Velacolib\Utility\Utility;
use Velacolib\Utility\TransactionUtility;
use Admin\Entity\OrderDetail;
use Admin\Entity\Orders;
use Admin\Entity\Table;
use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;


class OrderController extends BaseController
{
    protected   $modelOrder;
    protected   $modelOrderDetail;
    protected   $translator;


    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrineService = $this->sm->get($service_locator_str);
        $this->modelOrder = new orderModel($doctrineService);
        $this->modelOrderDetail = new orderdetailModel($doctrineService);
        $this->translator = Utility::translate();

        //check login
        //check login
        $user = Utility::checkLogin($this);
        if(! is_object($user) && $user == 0){
            $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }
        else{
            $isPermission = Utility::checkRole($user->userType,ROLE_ADMIN);
            if( $isPermission == false)
                $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }
        //end check login

        return parent::onDispatch($e);

    }


    public function ajaxListAction(){

        $fields = array(
            'id',
            'tableId',
            'userId',
            'newDate',
            'totalCost',
            'totalRealCost',
            'couponId',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn(3);
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = ' c.isdelete = 0 ';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }
        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\Orders c ";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Orders c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        // map data
        $ret = array_map(function($item) {
            $tableInfo = Utility::getTableInfo( $item->getTableid());
            $userInfo = Utility::getUserInfo($item->getUserId());
            $surtax = Utility::getSurTaxInfo($item->getSurtaxId());

            // surtax
            $surtax = Utility::getSurTaxInfo($item->getSurtaxId());
            $surtaxType = $surtax->getType();
            $taxType =  Utility::convertSurtaxType($surtaxType);

            // coupon
            $coupon = Utility::getCouponInfo($item->getCouponId());

            $couponValue = $coupon->getValue();
            $type = '';
            $couponType  = $coupon->getType();
            if($couponType == 0){
                $type = '';
            }elseif($couponType == 1){
                $type = '%' ;
            }



            // create link
            $linkEdit =   '/admin/Order/add/'.$item->getId() ;
            $linkDelete =  '/admin/Order/delete/'.$item->getId() ;
            $linkDetail =   '/admin/Order/detail/'.$item->getId() ;



            return array(
                'id' => $item->getId(),
                'tableId' => $tableInfo->getName() ,
                'userId' => $userInfo->getUserName() ,
                'newDate' =>$item->getNewDate(),
                'totalCost' => ($item->getTotalCost() ),
                'totalRealCost' =>  ($item->getTotalRealCost() ),
                'couponId' => $couponValue. ''.$type,
                'surtax' =>  ($surtax->getValue()) .' '.$taxType,
                'action'=> '
                <a href="'.$linkDetail.'" class="btn btn-info"><i class="icon-info-sign"></i></a>
                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>'
            );

        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }

    public function indexAction()
    {

        return new ViewModel(array('title'=>$this->translator->translate('Order')));

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
            TransactionUtility::rollbackTransaction($id);
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
            TransactionUtility::rollbackTransaction($orderId);
            echo 1;
            die;
        }
        echo 0;
        die;

    }

}