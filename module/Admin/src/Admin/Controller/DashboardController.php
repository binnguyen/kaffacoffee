<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;

use Admin\Entity\Categories;
use Admin\Entity\Table;
use Admin\Entity\Payment;
use Admin\Entity\MenuItem;
use Admin\Model\categoryModel;
use Admin\Model\menuModel;
use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Admin\Model\reportModel;
use Admin\Model\paymentModel;
use Admin\Model\menuItemModel;
use Admin\Model\trackingToreModel;
use Admin\Model\transactionModel;
use Admin\Model\paymentCategoryModel;
use Velacolib\Utility\Utility;
use Velacolib\Utility\renderExcel;
use Zend\Http\Headers;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Header;
use Zend\Http\Response\Stream;


class DashboardController extends AdminGlobalController
{
    protected $modelCategories;
    protected $modelOrder;
    protected $modelOrderDetail;
    protected $modelPayment;
    protected $modelTracking;
    protected $translator;
    protected $menuItem;
    protected $menuModel;
    public function init()
    {
        $doctrine = $this->doctrineService;
        $this->modelCategories = new categoryModel($doctrine);
        $this->modelOrder = new orderModel($doctrine);
        $this->modelOrderDetail = new orderdetailModel($doctrine);
        $this->modelPayment = new paymentModel($doctrine);
        $this->menuItem = new menuItemModel($doctrine);
        $this->modelTracking = new trackingToreModel($doctrine);
        $this->menuModel = new menuModel($doctrine);
    }
    public function indexAction(){
            $str = '';
            $strOrder = '';
            $strUser = '';
            $strMenu = '';
            $strMenuForAllMenu = '';
            $params = $this->params()->fromPost();
            $fromDate = date('Y-m-d');
            $fromDate = str_replace('-','/',$fromDate);
            $fromDate = date('Y-m-d',strtotime($fromDate . "-29 days"));
            $toDate = date('Y-m-d');
            if ($fromDate) {

                $fromDateTime = strtotime($fromDate . ' 00:00:00');
                $str .= ' AND table.createDate >= ' . $fromDateTime;
                $strOrder .= ' AND o.createDate >= ' . $fromDateTime;
            }

            if ($toDate) {

                $toDateTime = strtotime($toDate . ' 23:59:00');
                $str .= ' AND table.createDate <= ' . $toDateTime;
                $strOrder .= ' AND o.createDate <= ' . $toDateTime;

            }

            if (isset($params['menu']) && $params['menu'] != 0) {

                $strMenu .= ' AND mn.catId =' . $params['menu'];
                $strMenuForAllMenu .= ' AND table.id = od.orderId AND od.menuId = mn.id ' . $strMenu;
            }

//        }

        $year = date('Y');
        if ($this->params()->fromQuery('year')) {
            $year = $this->params()->fromQuery('year');
        }

//        $reportMonth = $this->modelOrder->reportAllMonth($year);

        //$categories = $this->modelCategories->findBy(array('isdelete'=>'0'));
        $reportUser = $this->modelOrder->createQuery('table.isdelete = 0 ' . $str . $strUser);
        $reportUser = reportModel::convertUserReportArray($reportUser);


        $reportTable = $this->modelOrder->createQueryTable('table.isdelete = 0 ' . $str . $strUser);
        $reportTable = reportModel::convertTableReportArray($reportTable);


        $reportMenu = $this->modelOrderDetail->createQueryMenu('table.isdelete = 0 ' . $strOrder . $strMenu,0,10);

        $reportMenu = reportModel::convertMenuReportArray($reportMenu);


        $reporAllOrder = $this->modelOrder->createQueryAllMenu('table.isdelete = 0 ' . $str . $strUser);

        /*  report order and count cost by menu id  */
        if (isset($params['menu']) && $params['menu'] != 0) {
            $reporAllOrder = $this->modelOrder->createQueryByMenu('table.isdelete = 0 ' . $str . $strUser . $strMenuForAllMenu,0,10);
        }


        $reportMenuType = $this->modelOrderDetail->createQueryMenuType('table.isdelete = 0 ' . $strOrder);
        $reportMenuType = reportModel::convertMenuTypeReportArray($reportMenuType);

//        //linkEcel
//        $link = renderExcel::renderMenuOrder($reportMenu);




        //setup data user table
        $dataUser = Utility::getUserForPieChart($reportUser);
        $dataUserCountOrder = Utility::getUserForPieChart($reportUser,'userId','count_user');
       // $dataUserCountOrder = Utility::getUserForPieChart($reportUser);

        //setup data table
        $dataTable = array(
            'tableTitle' => $this->translator->translate('Report table'),
            'link' => 'admin/order',
            'data' => $reportTable,
            'heading' => array(
                'tableId' => $this->translator->translate('Table name'),
                'count_table' => $this->translator->translate('Count number'),
            ),
            'hideEditButton' => 1,
            'hideDeleteButton' => 1,
            'hideDetailButton' => 1
        );

        //setup data menu
        $dataMenu = array(
            'tableTitle' => $this->translator->translate('Report menu'),
            'link' => 'admin/order',
            'data' => $reportMenu,
            'heading' => array(
                'menuName' => $this->translator->translate('Menu'),
                'count_menu' => $this->translator->translate('Count number'),
                'realCost' => $this->translator->translate('Real cost'),
            ),
            'hideEditButton' => 1,
            'hideDeleteButton' => 1,
            'hideDetailButton' => 1
        );


        $dataByDay = Utility::rsReportPerDay(30);
        return new ViewModel(array(
                'data_table' => $dataTable,
                'data_user' => $dataUser,
                'data_user_count_order' => $dataUserCountOrder,
                'data_menu' => $dataMenu,
                'title' => $this->translator->translate('Report'),
                'report_table_box' => $this->translator->translate('Report table'),
                'report_user_box' => $this->translator->translate('Report user'),
                'report_menu_box' => $this->translator->translate('Report menu'),
                'allOrder' => $reporAllOrder,
                'allOrderText' => $this->translator->translate('You have total:'),
                'allOrderTotalCostText' => $this->translator->translate('Total cost'),
                'allOrderTotalRealCostText' => $this->translator->translate('Total real cost'),
                'datetimeReport' => $this->translator->translate('Date time report'),
//                'reportMonth' => $reportMonth,
                'reportPerDay'=>$dataByDay
            )
        );
    }



}