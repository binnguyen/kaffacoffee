<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/8/14
 * Time: 10:22 PM
 */

namespace Velacolib\Utility;

use Admin\Entity\Coupon;
use Admin\Entity\Menu;
use Admin\Entity\MenuStore;
use Admin\Entity\MenuStoreMain;
use Admin\Entity\Property;
use Admin\Entity\Supplier;
use Admin\Entity\SupplierFor;
use Admin\Entity\SupplierItem;
use Admin\Entity\Surtax;
use Admin\Entity\User;
use Admin\Entity\UserHistory;
use Admin\Model\categoryModel;
use Admin\Model\configModel;
use Admin\Model\couponModel;
use Admin\Model\menuStoreMainModel;
use Admin\Model\menuStoreModel;
use Admin\Model\orderdetailModel;
use Admin\Model\orderModel;
use Admin\Model\propertyModel;
use Admin\Model\supplierModel;
use Admin\Model\supplyForModel;
use Admin\Model\supplyItemModel;
use Admin\Model\surTaxModel;
use Admin\Model\tableModel;
use Admin\Model\transactionModel;
use Admin\Model\userHistoryModel;
use Admin\Model\userModel;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\menuModel;
use Zend\Authentication\AuthenticationService;
use Admin\Entity\Orders;
use Admin\Entity\OrderDetail;
use Zend\I18n\Translator\Translator;
use Admin\Entity\MenuItem;
use Admin\Model\menuItemModel;
use Zend\Mail;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Validator\File\Size;


class Utility extends AbstractActionController
{
    public static $option;
    public static $servicelocator;

    public static function getSM()
    {
        return self::$servicelocator;
    }

    public static function setSM($val)
    {
        self::$servicelocator = $val;
    }

    public static function getMenuAdmin()
    {
        $table = self::$servicelocator->get('doctrine');
        echo '</pre>';
        $menu = new menuModel($table);
        echo '<pre>';
        print_r($menu->findAll());
        echo '</pre>';
    }

    public static function getCategories()
    {
        $table = self::$servicelocator->get('doctrine');
        $menu = new categoryModel($table);
        return $menu->findBy(array('isdelete' => '0'));
    }

    public static function getTables()
    {
        $table = self::$servicelocator->get('doctrine');
        $table = new tableModel($table);
        return $table->findBy(array('isdelete' => 0));
    }

    public static function getTableInfo($id)
    {
        $table = self::$servicelocator->get('doctrine');
        $table = new tableModel($table);
        return $table->findOneBy(array('id' => $id));
    }

    public static function getCatInfo($id)
    {
        $cat = self::$servicelocator->get('doctrine');
        $cat = new categoryModel($cat);
        return $cat->findOneBy(array('id' => $id));
    }

    public static function getMenu($showAll = 0)
    {
        $menus = self::$servicelocator->get('doctrine');
        $menus = new menuModel($menus);
        if($showAll == 0)
            return $menus->findBy(array('isdelete' => 0));
        return $menus->findAll();
    }

    public static function getMenuStoreArray()
    {
        $doctrine = self::$servicelocator->get('doctrine');
        $menusStoreModel = new menuStoreMainModel($doctrine);
        $menusStore = $menusStoreModel->findBy(array('isdelete' => 0));
        $menusStore = $menusStoreModel->convertToArray($menusStore);
        return $menusStore;
    }

    public static function getMenuStoreArrayAutoComplete($isAutoComplate = true){
        $menu = self::getMenuStoreArray();
        $return = array();
        if($isAutoComplate == false){
            foreach($menu as $item){
                $itemArray = array(
                    'name' => $item['name'],
                    'id' => $item['id'],
                    'unit' => $item['unit'],
                    'quantityInStock' => $item['quantityInStock'],
                    'outOfStock' => $item['outOfStock'],
                    'supplyType' => $item['supplyType'],
                );
                $return[] = $itemArray;
            }
        }else{
            foreach($menu as $item){
                $return[] = $item['name'];
            }
        }
        return $return;
    }

    public static function getMenuInfo($id)
    {
        if ($id == -1 || $id == 0) {
            return new Menu();
        }
        $doctrineService = self::$servicelocator->get('doctrine');
        $menus = new menuModel($doctrineService);
        $menus = $menus->findOneBy(array('id' => $id));
        if($menus)
            return $menus;
        return new Menu();

    }

    public static function checkRole($userRoleID, $contentRole)
    {
        $role = self::getUserRole($userRoleID);
        if (strtolower($contentRole) == strtolower($role)) {
            return true;
        }
        return false;
    }

    //check user role id vs content role

    public static function getUserRole($id = -1)
    {
        $role = array('1' => 'Admin', '0' => 'User');
        if ($id != -1) {
            return $role[$id];
        }
        return $role;
    }

    public static function checkLogin()
    {

        $Auth_service = new AuthenticationService();
        $auth = $Auth_service->getIdentity();

        if (!$auth) {
            return 0;
        } else {
            return $auth;
        }
    }


    //input controller param, controller request
    //output view model;

    public static function addNewOrder($param, $request, $url = 'admin/child')
    {
        $tables = self::$servicelocator->get('doctrine');
        $table = new orderModel($tables);
        $orderItem = new orderdetailModel($tables);
        $tableModel = new tableModel($tables);
        $transactionModel = new transactionModel($tables);
        $translator = self::translate();
        $id = $param->fromRoute('id');
        $catID = 0;
        $tableManage = $tableModel->findBy(array('isdelete'=>0));
        $save = $param->fromPost('save');
        $status = 'pending';
        $isdelete = 0;

        if($save == "payment"){
            $status = 'finish';
            $isdelete = 1;
        }

        //insert
        if ($id == '') {
            if ($request->isPost()) {

                //$detailRow = $param->fromPost('countChild');
                $Auth_service = new AuthenticationService();
                $auth = $Auth_service->getIdentity();
                $dataDetail = $param->fromPost('detail');
                $totalRealCost = $param->fromPost('total_real_cost');

                $cat = new Orders();
                $cat->setTotalCost($param->fromPost('total_cost'));
                $cat->setTableId($param->fromPost('table_id'));
                $cat->setCreateDate(time());
                $cat->setCouponId($param->fromPost('coupon_id'));
                $cat->setToTalRealCost($totalRealCost);
                $cat->setUserId($auth->userId);
                $cat->setSurtaxId($param->fromPost('surtax_id'));
                $cat->setIsdelete(0);
                $cat->setStatus($status);
                $cat = $table->insert($cat);
                $catID = $cat->getId();

                if ($param->fromPost('coupon_id') != -1) {
                    $doctrine = self::$servicelocator->get('doctrine');
                    $couponModel = new couponModel($doctrine);
                    $coupon = $couponModel->findOneBy(array('id' => $param->fromPost('coupon_id')));

                    if($coupon->getReuse() == 0){
                        if($save == 'payment'){
                            $coupon->setIsdelete($isdelete);
                            $couponModel->edit($coupon);
                        }

                    }

                }

                foreach ($dataDetail as $k => $val) {
                    if($val['menuid'] != -1){
                        self::insertOrderDetail($val,$catID);
                    }


                }
                if($save == 'payment'){
                    $url = "http://".$_SERVER['HTTP_HOST'].'/frontend/order/detail/'.$catID;
                    header("Location:".$url);  exit();
                }
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            return array(
                'title' => $translator->translate('Add new order'),
                'data' => null,
                'url' => $url,
                'orderId' => $catID  ,
                'tables'=>$tableManage ,
                'dataObject'=> array()  ,
            );
        } else {

            $cats = $table->findOneBy(array('id'=>$id));
            $orderDetail = $orderItem->findBy(array(
                'orderId'=>$id,
                'isdelete'=>0
            ));

            if($request->isPost()){

                $idFormPost = $param->fromPost('id');
                $cat = $table->findOneBy(array('id'=>$idFormPost));
                $dataDetail = $param->fromPost('detail');
                $Auth_service = new AuthenticationService();
                $auth = $Auth_service->getIdentity();
                $totalRealCost = $param->fromPost('total_real_cost');
                $totalCost =  $param->fromPost('total_cost');
                $tableId = $param->fromPost('table_id');
                $cat->setTotalCost($totalCost);
                $cat->setTableId($param->fromPost('table_id'));
                $cat->setCreateDate(time());
                $cat->setCouponId($param->fromPost('coupon_id'));
                $cat->setToTalRealCost($totalRealCost);
                $cat->setUserId($auth->userId);
                $cat->setSurtaxId($param->fromPost('surtax_id'));
                $cat->setIsdelete(0);
                $cat->setStatus($status);

                $table->edit($cat);

                if ($param->fromPost('coupon_id') != -1) {
                    $doctrine = self::$servicelocator->get('doctrine');
                    $couponModel = new couponModel($doctrine);
                    $coupon = $couponModel->findOneBy(array('id' => $param->fromPost('coupon_id')));

                    if($coupon->getReuse() == 0){
                        if($save == 'payment'){
                            $coupon->setIsdelete(1);
                            $couponModel->edit($coupon);
                        }

                    }

                }

                //update order
                // delete order detail
                $orderItem->deleteAll(array('orderId'=>$id));
                // insert order detail
                foreach ($dataDetail as $k => $val) {
                    if($val['menuid'] != -1){
                        self::insertOrderDetail($val,$idFormPost);
                    }
                }

                if($save == 'payment'){
                    $url = "http://".$_SERVER['HTTP_HOST'].'/frontend/order/detail/'.$id;
                    header("Location:".$url);  exit();
                } else{
                    $url = "http://".$_SERVER['HTTP_HOST'].'/frontend/order/add/'.$id.'?tbl='.$tableId;
                    header("Location:".$url);  exit();
                }


            }

            $cat = $table->convertSingleToArray($cats);

            return array(
                'title'=>'Edit Order',
                'data' =>  $cat,
                'url' =>$url  ,
                'orderDetails'=>$orderDetail,
                'orderId'=>0,
                'dataObject'=>$cats  ,
                'tables'=>$tableManage

            );
        }


    }



    static function translate($lang = null)
    {
        $lang = '';

        try{
            $config = Utility::getConfig();
            $lang = isset($config['lang'])?$config['lang']:'';
        }
        catch(\Exception $e){

        }
        if($lang == '')
            $lang = 'en_us';
        $type = 'Gettext';
        $pattern = $lang . '.mo';
        $base_dir = __DIR__ . '/../../../../../language/';
        $translator = new Translator();
        $translator->setLocale("en");
        $translator->addTranslationFilePattern($type, $base_dir, $pattern);
        return $translator;
    }

    static function  insertOrderDetail($data, $orderID)
    {


        $table = self::$servicelocator->get('doctrine');
        $table = new orderdetailModel($table);
        $orderDetail = new OrderDetail();

        $orderDetail->setOrderId($orderID);
        $orderDetail->setMenuId($data['menuid']);
        $orderDetail->setQuantity($data['quantity']);
        $orderDetail->setMenuCost($data['menuCost']);
        $orderDetail->setRealCost($data['realcost']);
        $orderDetail->setIsdelete(0);
        $orderDetail->setCostType($data['orderDetailType']);
        $orderDetail->setDiscount($data['discount']);
        $orderDetailInserted = $table->insert($orderDetail);
        //insert transaction


    }

    public static function getConfig()
    {
        $doctrine = self::$servicelocator->get('doctrine');
        $configs = new configModel($doctrine);
        $configs = $configs->findAll();
        $return = array();
        foreach ($configs as $config) {
            $return[$config->getName()] = $config->getValue();
        }
        return $return;
    }



    public static function getCouponType($id = -1)
    {
        $array = array('0' => 'Real value', '1' => 'Coupon percent');
        if ($id != -1)
            return $array[$id];
        return $array;
    }

    public static function getAllCoupon()
    {
        $doctrine = self::$servicelocator->get('doctrine');
        $couponModel = new couponModel($doctrine);
        return $couponModel->findBy(array( 'isdelete' => '0'  ));
    }

    static function getCouponCheckExpire(){
        $doctrine = self::$servicelocator->get('doctrine');
        $couponModel = new couponModel($doctrine);
        $now = strtotime(date("d-m-Y",time()));
        return $couponModel->getAllCoupon('table.isdelete = 0 AND table.todate >= '.$now.'');

    }

    public static function queryCoupons($query = array())
    {
        $doctrine = self::$servicelocator->get('doctrine');
        $couponModel = new couponModel($doctrine);
        return $couponModel->findBy($query);
    }


    public static function getCouponInfo($couponId)
    {
        $doctrine = self::$servicelocator->get('doctrine');
        $couponModel = new couponModel($doctrine);
        $counpon = $couponModel->findOneBy(array('id' => $couponId));
        if($counpon)
            return $counpon;
        return new Coupon();
    }

    public static function getMenuCostType($typeId = -1)
    {

        $translator = self::translate();
        $array = array('0' => $translator->translate('Take away'),
            '1' => $translator->translate('Stay here'));
        if ($typeId != -1) {
            return $array[$typeId];
        }
        return $array;
    }


    public static function getCombo($type)
    {
        if ($type == 1)
            return 'combo';
        return '';
    }

    public static function  getUserInfo($userId)
    {

        $doctrine = self::$servicelocator->get('doctrine');
        $userModel = new userModel($doctrine);
        $user = $userModel->findOneBy(array('id' => $userId));
        if ($user) {
            return $user;
        } else {
            return new User();
        }
    }

    public static function  getUser()
    {

        $doctrine = self::$servicelocator->get('doctrine');
        $userModel = new userModel($doctrine);
        $user = $userModel->findAll();
        if ($user) {
            return $user;
        } else {
            return new User();
        }
    }

    static function getMenuValue($id,$type = 1){
        $doctrine = self::$servicelocator->get('doctrine');
        $menuModel = new menuModel($doctrine);
        $menu = $menuModel->findOneBy(array(
            'id'=>$id,
        ));
        if($menu){
            if($type == 1){
                $value = $menu->getCost();
            }elseif($type = 0){
                $value = $menu->getTakeAwayCost();
            }
        }else{
            $value = null;
        }


        return $value;

    }



    public static function getSurTax()
    {
//        $surTax = self::$servicelocator->get('doctrine');
//        $surTax = new surTaxModel($surTax);
//        return $surTax->findAll();
    }


    public static function getSurTaxInfo($id)
    {


    }

    public static function getSurtaxType($typeId = '')
    {

        $translator = self::translate();
        $array = array('percent' => $translator->translate('Percent'),
            'cash' => $translator->translate('Cash'));
        if ($typeId != '') {
            return $array['' . $typeId . ''];
        }
        return $array;
    }

    public static function getMenuItem()
    {
        $menuItem = self::$servicelocator->get('doctrine');
        $menuItem = new menuItemModel($menuItem);
        return $menuItem->findAll();
    }

    public static function getMenuItemInfo($id)
    {
        $doctrineService = self::$servicelocator->get('doctrine');
        $menuItem = new menuItemModel($doctrineService);
        $menuItem = $menuItem->findOneBy(array('id' => $id));
        if ($menuItem)
            return $menuItem;
        return new MenuItem();

    }

    public static function getMenuStore()
    {
        $menuItem = self::$servicelocator->get('doctrine');
        $menuItem = new menuStoreModel($menuItem);
        return $menuItem->findBy(array('isdelete'=>'0'));
    }

    public static function getMenuStoreInfo($id)
    {
        $doctrineService = self::$servicelocator->get('doctrine');
        $menuItem = new menuStoreModel($doctrineService);
        $menuItem = $menuItem->findOneBy(array('id' => $id));
        if ($menuItem)
            return $menuItem;
        return new MenuStore();

    }

    public static function addMenuItem($param, $request)
    {
        $request = $request->getRequest();
        $result = null;
        if ($request->isPost()) {
            $table = self::$servicelocator->get('doctrine');
            $table = new menuItemModel($table);
            $menuItem = new MenuItem();
            $menuItem->setMenuStoreId($param->fromPost('menu_store_id'));
            $menuItem->setMenuId($param->fromPost('menu_id'));
            $menuItem->setQuantity($param->fromPost('quantity'));
            $menuItem->setUnit($param->fromPost('unit'));
            $tableInserted = $table->insert($menuItem);
            $result = $tableInserted;

        }
        return $result;
    }

    public static function convertSurtaxType($type)
    {
        $config = self::getConfig();
        $return = $config['currency'];

        if ($type == 'percent') {
            $return = " % ";
        }
        return $return;
    }

    public static function getStoreInfo($storeId)
    {
        $doctrine = self::$servicelocator->get('doctrine');
        $storeModel = new menuStoreModel($doctrine);
        $store = $storeModel->findOneBy(array('id' => $storeId));
        if ($store)
            return $store;
        return new MenuStore();
    }
    public  static function getMainStoreInfo($storeId){
        $doctrine = self::$servicelocator->get('doctrine');
        $storeModel = new menuStoreMainModel($doctrine);
        $store = $storeModel->findOneBy(array('id' => $storeId));
        if ($store)
            return $store;
        return new MenuStoreMain();
    }

    public  static function getMainStores(){
        $doctrine = self::$servicelocator->get('doctrine');
        $storeModel = new menuStoreMainModel($doctrine);
        $store = $storeModel->findBy(array('isdelete' => '0'));
        return $store;

    }

    public static function getPropertyInfo($storeId)
    {
        $doctrine = self::$servicelocator->get('doctrine');
        $storeModel = new propertyModel($doctrine);
        $store = $storeModel->findOneBy(array('id' => $storeId));
        if ($store)
            return $store;
        return new Property();
    }

    public static function getProperty()
    {
        $doctrine = self::$servicelocator->get('doctrine');
        $storeModel = new propertyModel($doctrine);
        $store = $storeModel->findAll();
        if ($store)
            return $store;
        return new Property();
    }

    public static function generateCouponCode()
    {
        $code = sha1(uniqid(rand(), true));
        // return 8 character code
        return strtoupper(substr($code, 2, -30));
    }

    public static function sendEmail($templateName, $data, $subject, $receiveEmail, $smtp = true)
    {
        // setup SMTP options
        $config = self::getConfig();
        $options = new Mail\Transport\SmtpOptions(array(
            'name' => 'localhost',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'connection_class' => 'login',
            'connection_config' => array(
                'username' => $config['emailId'],
                'password' => $config['emailPassword'],
                'ssl' => 'tls',
            ),
        ));

        if ($smtp) {
            $senderEmail = $config['emailId'];
            $senderName = $config['emailId'];
        } else {
            $senderEmail = $config['emailId'];
            $senderName = 'Kaffa - Coffee & more';
        }

        $render = self::$servicelocator->get('ViewRenderer');
        $content = $render->render('email/' . $templateName, array('data' => $data));

// make a header as html
        $html = new MimePart($content);
        $html->type = "text/html";
        $body = new MimeMessage();
        $body->setParts(array($html,));

// instance mail
        $mail = new Mail\Message();
        $mail->setBody($body); // will generate our code html from template.phtml
        $mail->setFrom($senderEmail, $senderName);
        $mail->setTo($receiveEmail);
        $mail->setSubject($subject);

        if ($smtp) $transport = new Mail\Transport\Smtp($options);
        else {
            $transport = new Mail\Transport\Sendmail();
        }
        $status = $transport->send($mail);
        return $status;

//        $transport = new Mail\Transport\Smtp($options);
//        $transport->send($mail);
    }

    public static function insertHistory($action)
    {
    }

    public static function roundCost($cost)
    {
        $config = self::getConfig();

        // $roundCost = (ceil($cost / 1000)) * 1000;
        $cost = number_format($cost,$config['number_decimal']);
        return $cost;
    }
    public static function formatCost($cost){
        $config = self::getConfig();
        $currency = $config['currency'];
        $currency_before = $config['currency_before'];
        $costFormated = self::roundCost($cost);
        if($currency_before == 1)
            return $currency.' '.$costFormated;
        return $costFormated.' '.$currency;
    }

    public static  function getSupplierArray(){
        $doctrine = self::$servicelocator->get('doctrine');
        $supliersModel = new supplierModel($doctrine);
        $sups =  $supliersModel->findBy(array('isdelete'=>'0'));
        return $supliersModel->convertToArray($sups);
    }

    public static  function getSupplierInfo($id){

        $doctrine = self::$servicelocator->get('doctrine');
        $supliersModel = new supplierModel($doctrine);
        $sups =  $supliersModel->findOneBy(
            array(
                'id'=>$id
            )
        );

        if($sups){

            return $sups;
        }
        return new Supplier();
    }


    public static function  getAllSuplyItemsArray(){
        $doctrine = self::$servicelocator->get('doctrine');
        $supliersItemModel = new supplyItemModel($doctrine);
        $sups =  $supliersItemModel->findBy(array('isdelete'=>'0'));
        $array = array();
        foreach($sups as $sup){
            $array[$sup->getId()] =   $sup->getValue();
        }
        return $array;
    }

    public static function countOrderDetail($id){
        $doctrine = self::$servicelocator->get('doctrine');
        $supliersItemModel = new orderdetailModel($doctrine);
        $sups =  $supliersItemModel->countQuantityByMenuId($id);
        return $sups;
    }


    public static  function getSupplyItemOfSupplier($supplierId){
        $doctrine = self::$servicelocator->get('doctrine');
        $suplyForModel = new supplyForModel($doctrine);
        $doctrine = self::$servicelocator->get('doctrine');
        $supliersItemModel = new supplyItemModel($doctrine);
        $supplyFor = $suplyForModel->findBy(array('suppilerId'=>$supplierId));
        $arr = array();
        foreach($supplyFor as $item){
            $itemSup =  $supliersItemModel->findOneBy(array('id'=> $item->getSupplierItem()));
            if($itemSup){
                $arr[] =  array(
                    'id' =>      $itemSup->getId(),
                    'name' => $itemSup->getValue()
                );
            }
            // $itemSup = new SupplierItem();
        }
        return $arr;
    }

    public static function getSupplierBySupplyItem($supplyItemId){
        $doctrine = self::$servicelocator->get('doctrine');
        $suplyItemModel = new supplyForModel($doctrine);
        $supplyItems = $suplyItemModel->findBy(array('supplierItem'=>$supplyItemId));

        if($supplyItems)
            return $supplyItems;
        return new SupplierItem();
    }

    public static  function getUnitArray(){
        return array(
            'KG' => 'Kilograms',
            'G' => 'Grams',
            'MG' => 'Milligram',
            'L' => 'Liter',
            'ML' => 'Milliliter',
            'Goi' => 'Goi',
            'Hu' => 'Hu',
            'Cai' => 'Cai',
            'Trai' => 'Trai'
        );
    }




    public static function getTableStatus($tableId){
        $table = self::$servicelocator->get('doctrine');

        $tableModel = new orderModel($table);
        $Auth_service = new AuthenticationService();
        $auth = $Auth_service->getIdentity();
        $checkStatus =   $tableModel->findOneBy(array(
            'tableId'=>$tableId,
            'status' =>'pending',
            'userId'=>$auth->userId

        ));
        $return  =  array();
        $link = 'http://'.$_SERVER['HTTP_HOST'].'/frontend/order/add';
        if(empty($checkStatus)){
            $return['status'] = 'Finish';
            $return['id']    =0;
            $return ['link'] =  $link.'?tbl='.$tableId ;
            $return['background'] = 'green-background';
            $return['cost'] = 0;
        }else{
            $return['status'] = 'pending';
            $return['id']    = $checkStatus->getId();
            $return['link'] = $link.'/'.$checkStatus->getId().'?tbl='.$tableId;
            $return['background'] = 'red-background';
            $return['cost'] = number_format($checkStatus->getTotalRealCost());
        }
        return $return;
    }

    public static function getOrderPending(){

        $table = self::$servicelocator->get('doctrine');

        $tableModel = new orderModel($table);
        $Auth_service = new AuthenticationService();
        $auth = $Auth_service->getIdentity();
        $orders =   $tableModel->findBy(array(
            'status' =>'pending',
            'userId'=>$auth->userId
        ));
        return $orders;

    }

    public static function getStaff(){

        $table = self::$servicelocator->get('doctrine');

        $tableModel = new userModel($table);


        $staff =   $tableModel->findBy(array(
            'type'=>0,
            'isdelete'=>0
        ));

        return $staff;

    }

    public static  function getMenuStoreInMenu($menuId){

    }

    public static function getMenuInMenuStore($meuStoreId){
        $doctrine = self::$servicelocator->get('doctrine');
        $menuItemTable = new MenuItemModel($doctrine);
        $menuItems = $menuItemTable->findBy(array('menuStoreId'=>$meuStoreId));
        $html = '<ul>';
        foreach($menuItems as $menuItem){
            $menuInfo = self::getMenuInfo($menuItem->getMenuId());
            $html .= '<li><b><a href="/admin/index/add/'.$menuInfo->getId().'" target="_blank"> '.$menuInfo->getName().'</a></b>: ' .$menuItem->getQuantity().'('.$menuItem->getUnit().')</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public static function groupOrderId(){
        $doctrine = self::$servicelocator->get('doctrine');
        $orderModel = new orderdetailModel($doctrine);
        $array = $orderModel->groupOrder();

        $return = array();
        foreach($array as $item){
            $return[$item['orderId']] = $item['count_table'];
        }
        return $return;
    }

    public static  function  checkMergOrder(){

        $doctrine = self::$servicelocator->get('doctrine');
        $orderModel = new orderModel($doctrine);
        $Auth_service = new AuthenticationService();
        $auth = $Auth_service->getIdentity();
        $orderModel->findBy(array(
            'status'=>'pending' ,
            'user_id'=> $auth->userId
        ));


    }

    public static function getPriceUseCoupon($price = 0,$couponId = 0){
        $doctrine = self::$servicelocator->get('doctrine');
        $couponModel = new couponModel($doctrine);
        $couponDetail = $couponModel->findOneBy(array(
            'id'=>$couponId
        ));
        $newPrice = self::roundCost($price);
        if(!empty($couponDetail)){
            if($couponDetail->getType() == 0){
                $newPrice = self::roundCost($price - $couponDetail->getValue());
            }elseif($couponDetail->getType() == 1){
                $newPrice = self::roundCost($price - (($price * $couponDetail->getValue())/100));
            }
        }elseif($couponId == -1){
            $newPrice = self::roundCost($price);
        }
        return $newPrice;

    }

    /**
     * @param $file
     */
    static function uploadFile($file){
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $size = new Size(array('max'=>2000000)); //minimum bytes filesize
        $adapter->setValidators(array($size), $file['avatar']['name']);
        $data = array();
        if ($adapter->isValid()){

            $adapter->setDestination($_SERVER['DOCUMENT_ROOT'].'/img/upload');
            if ($adapter->receive($file['avatar']['name'])) {
                $data['avatar'] = 'http://'.$_SERVER['HTTP_HOST'].'/img/upload/'.$file['avatar']['name'];
                $data['status'] = true;
                $data['error'] = null;
            }

        }  else{
            $dataError = $adapter->getMessages();
            $error = array();
            foreach($dataError as $key=>$row)
            {
                $error[] = $row;
            } //set formElementErrors
            $data['avatar'] = '';
            $data['status'] = false;
            $data['error'] = $error;
        }
        return $data;


    }


    static function getImage($size = 'thumb',$urlImage){
        $image = '';
        switch ($size){
            case 'thumb':
                return $image = '<img src="'.$urlImage.'" width="50" />';
                break;
            case 'normal':
                return $image = '<img src="'.$urlImage.'" width="250" />';
                break;
            case 'full':
                return $image = '<img src="'.$urlImage.'" />';
                break;
            default:
                return $image = '<img src="'.$urlImage.'"  />';
                break;
        }

    }

    public static  function getCategoryForSelect(){
        $doctrineService = self::$servicelocator->get('doctrine');
        $categoryModel = new categoryModel($doctrineService);
        $cat = $categoryModel->findBy(array('isdelete'=>0));
        $return = array();
        foreach($cat as $item){
            $return[$item->getId()] = $item->getName();
        }
        return $return;
    }

    public static  function checkUserExist($userName){
        $doctrineService = self::$servicelocator->get('doctrine');
        $userModel = new userModel($doctrineService);
        $user = $userModel->findOneBy(array('userName'=>$userName));
        if($user)
            return true;
        return false;
    }

    public static function renderTableIcon($class,$status,$table){?>
        <div class="span2 <?= $class ?>">
            <div class="row row-cus">
                <div class="span12 icon-content">
                    <div data-order-id="<?php echo $status['id'] ?>"
                         class="muted cancel-order icon-remove align-right"></div>
                    <?php  if (strtolower($status['status']) != strtolower('Finish')) {   ?>
                        <!-- link switch user -->
                        <a href="#user-modal" class="switch-order-user " title="Switch invoice user"
                           data-toggle="modal" data-order-id="<?php echo $status['id'] ?>">
                            <div class="muted  icon-briefcase align-left" style="float: left"></div>
                        </a>

                        <!-- link merge order -->
                        <a href="#merge-modal" class="merge-order " data-toggle="modal"
                           data-order-id="<?php echo $status['id'] ?>" data-table="<?= $table->getId(); ?>"
                           title="Merge Invoice">
                            <div class="muted  icon-refresh align-left" style="float: left"></div>
                        </a>
                        <!-- -->
                        <a href="#split-modal" class="split-order " data-toggle="modal"
                           data-order-id="<?php echo $status['id'] ?>"
                           data-table="<?php echo $table->getId(); ?>" title="Split Invoice">
                            <div class="muted  icon-random align-left" style="float: left"></div>
                        </a>
                    <?php    } ?>
                </div>
            </div>
            <a href="<?= $status['link'] ?>" class="" style="text-decoration: none">
                <div class="box-content box-statistic <?= $status['background'] ?>">
                    <h3 class="title text-error"><?php echo $table->getName() ?></h3>
                    <small ><?= $status['status'] ?></small>
                    <small class="pull-right"><?= $status['cost']  ?></small>
                    <div class="text-error icon-inbox align-right"></div>
                </div>
            </a>

        </div>
    <?php
    }

    public function getTableForSelect(){
        $tables = self::getTables();
        $arrayReturn = array();
        foreach($tables as $table){
            $arrayReturn[$table->getId()] = $table->getName();
        }
        return $arrayReturn;
    }
}


