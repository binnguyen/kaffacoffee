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


class ReportnewController extends AdminGlobalController
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
            $fromDate = date('Y-m-d',strtotime($fromDate . "-30 days"));
            $toDate = date('Y-m-d');
            // $user = $params['user'];
            $strUserOrder = '';
            $userText = '';
            $fromText = '';
            $toText = '';
            $menuText = '';

       // if ($this->getRequest()->isPost()) {

//            $params = $this->params()->fromPost();
//            $fromDate = $params['formDate'];
//            $toDate = $params['toDate'];
//            $user = $params['user'];
//            $strUserOrder = '';

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
            if (isset($params['user']) && $params['user'] != 0) {
                $strUserOrder .= ' AND table.userId = ' . $user;
                $strUser .= ' AND table.userId = ' . $user;
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

        $reportMonth = $this->modelOrder->reportAllMonth($year);

        //$categories = $this->modelCategories->findBy(array('isdelete'=>'0'));
        $reportUser = $this->modelOrder->createQuery('table.isdelete = 0 ' . $str . $strUser);
        $reportUser = reportModel::convertUserReportArray($reportUser);


        $reportTable = $this->modelOrder->createQueryTable('table.isdelete = 0 ' . $str . $strUser);
        $reportTable = reportModel::convertTableReportArray($reportTable);


        $reportMenu = $this->modelOrderDetail->createQueryMenu('table.isdelete = 0 ' . $strOrder . $strMenu,0,10);

        $reportMenu = reportModel::convertMenuReportArray($reportMenu);

        $reportPayment = $this->modelPayment->createQuery('table.id != 0');

        $reportPayment = reportModel::convertPaymenttArray($reportPayment);


        $reporAllOrder = $this->modelOrder->createQueryAllMenu('table.isdelete = 0 ' . $str . $strUser);

        /*  report order and count cost by menu id  */
        if (isset($params['menu']) && $params['menu'] != 0) {
            $reporAllOrder = $this->modelOrder->createQueryByMenu('table.isdelete = 0 ' . $str . $strUser . $strMenuForAllMenu,0,10);
        }


        $reportMenuType = $this->modelOrderDetail->createQueryMenuType('table.isdelete = 0 ' . $strOrder);
        $reportMenuType = reportModel::convertMenuTypeReportArray($reportMenuType);

        //linkEcel
        $link = renderExcel::renderMenuOrder($reportMenu);


        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        //$dataRow = $this->modelCategories->convertToArray($categories);

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

        //setup data cost type menu
        $dataMenuCostType = array(
            'tableTitle' => $this->translator->translate('Report menu'),
            'link' => 'admin/order',
            'data' => $reportMenuType,
            'heading' => array(
                'costType' => $this->translator->translate('Cost type'),
                'cost_type_quantity' => $this->translator->translate('Count number'),
                'realCost' => $this->translator->translate('Real cost'),
            ),
            'hideEditButton' => 1,
            'hideDeleteButton' => 1,
            'hideDetailButton' => 1
        );

        //setup data Payment table
        $dataPayment = array(
            'tableTitle' => $this->translator->translate('Report payment'),
            'link' => 'admin/payment',
            'data' => $reportPayment,
            'heading' => array(
                'title' => $this->translator->translate('Title'),
                'value' => $this->translator->translate('Value'),
                'reason' => $this->translator->translate('Reason'),
                'time' => $this->translator->translate('Time'),
            ),
            'hideEditButton' => 1,
            'hideDeleteButton' => 1,
            'hideDetailButton' => 1
        );


        return new ViewModel(array(
                'data_table' => $dataTable,
                'data_user' => $dataUser,
                'data_user_count_order' => $dataUserCountOrder,
                'data_menu' => $dataMenu,
                'data_menu_costtype' => $dataMenuCostType,
                'data_payment' => $dataPayment,
                'title' => $this->translator->translate('Report'),
                'report_table_box' => $this->translator->translate('Report table'),
                'report_user_box' => $this->translator->translate('Report user'),
                'report_menu_box' => $this->translator->translate('Report menu'),
                'payment_menu_box' => $this->translator->translate('Report payment'),
                'allOrder' => $reporAllOrder,
                'allOrderText' => $this->translator->translate('You have total:'),
                'allOrderTotalCostText' => $this->translator->translate('Total cost'),
                'allOrderTotalRealCostText' => $this->translator->translate('Total real cost'),
                'datetimeReport' => $this->translator->translate('Date time report'),
                'fromDateText' => $this->translator->translate('From date'),
                'allMonthInYearText' => $this->translator->translate('All month in year'),
                'toDateText' => $this->translator->translate('To date'),
                'submitText' => $this->translator->translate('Report'),
                'reportMonth' => $reportMonth,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'excelLink' => $link
            )
        );
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if ($id == '') {
            if ($request->isPost()) {
                $cat = new Categories();
                $cat->setName($this->params()->fromPost('name'));
                $catInserted = $this->modelCategories->insert($cat);
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            return new ViewModel(array('title' => $this->translator->translate('Add new category')));
        } else {

            $cat = $this->modelCategories->findOneBy(array('id' => $id));
            if ($request->isPost()) {
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelCategories->findOneBy(array('id' => $idFormPost));
                $cat->setName($this->params()->fromPost('name'));
                $this->modelCategories->edit($cat);
            }
            return new ViewModel(array(
                'data' => $cat,
                'title' => $this->translator->translate('Edit Category') . ': ' . $cat->getName()
            ));
        }
    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->params()->fromPost('id');
            $menu = $this->modelCategories->findOneBy(array('id' => $id));
            $menu->setIsdelete(1);
            $this->modelCategories->edit($menu);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {
        //get user by id
        $id = $this->params()->fromRoute('id');
        $user = $this->model->findOneBy(array('id' => $id));
        $user->setFullName('tri 1234');
        $this->model->edit($user);
        //update user

    }
    public function exportAction()
    {
        $reportOrder = $this->modelOrderDetail->createQuery();
        $link = renderExcel::renderReportMenu($reportOrder);
        echo '<a href="/' . $link . '">Download</a>';
        die;
    }
    public function exportMenuAction()
    {
        $str = '';
        $strOrder = '';
        $fromDate = '';
        $toDate = '';
        if ($this->getRequest()->isPost()) {
            $params = $this->params()->fromPost();
            $fromDate = $params['formDate'];
            $toDate = $params['toDate'];
            if ($fromDate) {
                $fromDateTime = strtotime($fromDate . ' 00:00:00');
                $strOrder .= ' AND o.createDate >= ' . $fromDateTime;
            }

            if ($toDate) {
                $toDateTime = strtotime($toDate . ' 23:59:00');
                $strOrder .= ' AND o.createDate <= ' . $toDateTime;
            }
        }
        $str .= 'o.isdelete = 0';
        $reportMenus = $this->modelOrderDetail->createQueryMenu($str . $strOrder);
        $link = renderExcel::renderMenuOrder($reportMenus);
        echo '<a href="/' . $link . '">Download</a>';
        die;
    }
    public function userReportAction()
    {


    }
    public function menuAction()
    {

        if ($this->getRequest()->isPost()) {

            $menuId = $this->params()->fromPost('menu');
            $data = $this->params()->fromPost();
            $data['start'] == 0 ? $start = 0 : $start = date('Y-m-d', strtotime($data['start']));
            $data['end'] == 0 ? $end = 0 : $end = date('Y-m-d', strtotime($data['end']));

            $menuItem = $this->menuItem->findBy(array(
                'menuStoreId' => $menuId
            ));
            if (isset($data['export']) && $data['export'] == true) {

            }

            return new ViewModel(array(
                'menuId' => $menuId,
                'menuItem' => $menuItem,
                'start' => $start,
                'end' => $end,
            ));


        }

    }
    public function paymentAction()
    {


    }

    //start tri
    public function reportCategoriesAction()
    {

        if ($this->getRequest()->isPost()) {


            $menuId = $this->params()->fromPost('menu');
            $data = $this->params()->fromPost();

            //format search day
            $data['start'] == 0 ? $start = 0 : $start = date('Y-m-d', strtotime($data['start']));
            $data['end'] == 0 ? $end = 0 : $end = date('Y-m-d', strtotime($data['end']));
            $reports = $this->modelCategories->reportCategories($start, $end);
            $totalCost = 0;
            $totalQuantity = 0;
            $reportTitle = 'Report by category in ' . date('d ,M Y', strtotime($start)) . ' to ' . date('d ,M Y', strtotime($end));
            $column = array(
                'category_id' => 'Category id',
                'category_name' => 'Category name',
                'Quantity' => 'Quantity',
                'cost' => 'Cost',
                'order_create_time' => 'Order create time'
            );
            $link = false;
            if (isset($data['excel']) && $data['excel'] == true) {
                $link = renderExcel::renderExcelBasic($reports, $column, $reportTitle);
//               return self::forceDownloadAction($link);
                $link = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $link;
            }


            foreach ($reports as $report) {
                $totalCost += $report['cost'];
                $totalQuantity += $report['Quantity'];
            }
            return new ViewModel(array(
                'menuId' => $menuId,
                'report' => $reports,
                'start' => $start,
                'end' => $end,
                'totalCost' => $totalCost,
                'totalQuantity' => $totalQuantity,
                'linkDownload' => $link,
                'title' => $reportTitle
            ));


        }

    }
    public function reportMenuDateRangeAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            //format search day
            $data['start'] == 0 ? $start = 0 : $start = date('Y-m-d', strtotime($data['start']));
            $data['end'] == 0 ? $end = 0 : $end = date('Y-m-d', strtotime($data['end']));
            $reports = $this->modelOrderDetail->reportMenuByDateRange($start, $end);
            $reportTitle = 'Report by menu in ' . $start . ' - ' . $end;
            $column = array(
                'menu_id' => 'Menu id',
                'name' => 'Name',
                'quantity' => 'Quantity',
                'realCost' => 'Real Cost',
            );
            $link = renderExcel::renderExcelBasic($reports, $column, $reportTitle);
            $totalCost = 0;
            $totalQuantity = 0;
            foreach ($reports as $report) {
                $totalCost += $report['realCost'];
                $totalQuantity += $report['quantity'];
            }
            return new ViewModel(array(
                'report' => $reports,
                'start' => $start,
                'end' => $end,
                'totalCost' => $totalCost,
                'totalQuantity' => $totalQuantity,
                'linkDownload' => 'http://' . $_SERVER['HTTP_HOST'] . '/' . $link,
                'reportTitle' => $reportTitle
            ));


        }
    }
    public function reportTrackingAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            //format search day
            $data['start'] == 0 ? $start = 0 : $start = date('Y-m-d', strtotime($data['start']));
            $data['end'] == 0 ? $end = 0 : $end = date('Y-m-d', strtotime($data['end']));
            $reports = $this->modelTracking->reportTracking($start, $end);
            $reportTitle = 'Report by tracking in ' . $start . ' - ' . $end;
            $column = array(
                'tracking_id' => ' id',
                'tracking_name' => 'Name',
                'tracking_quantity' => 'Quantity',
                'supplier_item_id' => 'Supplier item id',
                'supplier_item_name' => 'Supplier item name',
                'note' => 'Note',
                'time' => 'Time',
            );
            $link = renderExcel::renderExcelBasic($reports, $column, $reportTitle);
            $totalCost = 0;
            $totalQuantity = 0;

            return new ViewModel(array(
                'report' => $reports,
                'start' => $start,
                'end' => $end,
                'totalCost' => $totalCost,
                'totalQuantity' => $totalQuantity,
                'linkDownload' => 'http://' . $_SERVER['HTTP_HOST'] . '/' . $link,
                'reportTitle' => $reportTitle
            ));


        }
    }
    //end tri

    /**
     * @param $file
     * @return Stream
     */
    function forceDownloadAction($file)
    {
//        $file = '/path/to/my/file.txt';
        $file = $_SERVER['DOCUMENT_ROOT'] . '/' . $file;
        $response = new Stream();
        $response->setStream(fopen($file, 'r'));
        $response->setStatusCode(200);
        $response->setStreamName(basename($file));

        $headers = new Headers();
        $headers->addHeaders(array(
            'Content-Disposition' => 'attachment; filename="' . basename($file) . '"',
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => filesize($file)
        ));
        $response->setHeaders($headers);
        return $response;
    }
    public function reportExpenseAction()
    {
        $allCategory = Utility::getPaymentCate();
        $result = array();
        $link = false;
        $reportTitle = '';
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $categoryId = $data['expense_category'];
            $strTime = ' ';
            $strCategory = '' ;
            if($categoryId != 0){
                $strCategory .= ' table.categoryId = '.$categoryId ;
            }else{
                $strCategory .=  '  table.categoryId != -1 ';
            }
            if($data['start'] != '' || $data['end'] != '') {
                $strTime .= ' AND table.time >= '.strtotime($data['start']);
                $strTime .= ' AND table.time <= '.strtotime($data['end']);
            }

            $result = $this->modelPayment->createQueryToArray( $strCategory . $strTime );

            $category = Utility::getPaymentCateInfo($categoryId);

            $reportTitle = 'Report Expense in ' . $data['start'] . ' to ' . $data['end'] .' by Category '.$category->getName() ;
            if(isset($data['excel']) && $data['excel'] == true){

               // $reportTitle = 'Report Expense in ' . $data['start'] . ' to ' . $data['end'] .' by Category '.$category->getName() ;
              // $dataExcel = $this->modelPayment->convertToArray($result);
                $column = array(
                    'id' => ' id',
                    'title' => 'Name',
                    'value' => 'Quantity',
                    'reason' => 'Reason',
                    'time' => 'Time',
                    'categoryId' => 'Category',
                );
                $link = renderExcel::renderExcelBasic($result, $column, $reportTitle);
                return self::forceDownloadAction($link);

            }

        }
        return new ViewModel(array(
            'title'=>$reportTitle,
            'result'=>$result  ,
            'categories'=>$allCategory   ,
            'linkDownload' => $link
        ));
    }

}