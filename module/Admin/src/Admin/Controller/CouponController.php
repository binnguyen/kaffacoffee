<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Coupon;
use Admin\Entity\Menu;
use Admin\Entity\Managetable;
use Velacolib\Utility\Utility;
use Admin\Model\couponModel;
use Zend\View\Model\ViewModel;
use Velacolib\Utility\Table\AjaxTable;
use Zend\Mvc\Controller\AbstractActionController;


class CouponController extends AdminGlobalController
{
    protected   $modelCoupon;
    protected   $translator;
    public function init(){

        $this->modelCoupon = new couponModel($this->doctrineService);
    }


    public function indexAction()
    {
        //config table
        /////column for table
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Code', 'db' => 'code','dt' => 1, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Value', 'db' => 'value','dt' => 2, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Type', 'db' => 'type','dt' => 3, 'search'=>true, 'type' => 'text',   'dataSelect' => Utility::getCouponType() ),
            array('title' =>'From Date', 'db' => 'fromdate','dt' => 4, 'search'=>true, 'type' => 'text' ),
            array('title' =>'To Date', 'db' => 'todate','dt' => 5, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Action', 'db' => 'id','dt' => 6, 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/coupon';
                    return '

                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a class="btn-xs action action-detail btn btn-danger  " href="'.$actionUrl.'/delete/'.$d.'"><i class="icon-remove"></i></a>
                    ';
                }
            )

        );

        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/coupon');
        $table->setTablePrefix('m');
        $table->setExtendSQl(array(
            array('AND','m.isdelete','=','0'),
        ));
        $table->setAjaxCall('/admin/coupon');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelCoupon);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Manage Coupon')));
    }



    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            if($request->isPost()) {
               $data = $this->params()->fromPost();
               $coupon = new Coupon();
                $counponCode = Utility::generateCouponCode();
                $coupon->setCode($counponCode);
                $coupon->setValue($data['Value']);
                $coupon->setType($data['type']);
                $coupon->setIsdelete(0);
                $coupon->setDescription($data['description']);
                $coupon->setFromdate(strtotime($data['fromdate']));
                $coupon->setTodate(strtotime($data['todate']));
                $coupon->setReuse($data['reuse']);
                $this->modelCoupon->insert($coupon);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'coupon'));
            }
            //insert new user

            return new ViewModel(array('title'=> $this->translator->translate('Add New Coupon')));
        }
        else{

            $coupon = $this->modelCoupon->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $data = $this->params()->fromPost();
//                print_r($data);
                $idFormPost = $data['id'];
                $coupon = $this->modelCoupon->findOneBy(array('id'=>$idFormPost));
                $coupon->setCode($data['code']);
                $coupon->setValue($data['Value']);
                $coupon->setType($data['type']);
                $coupon->setDescription($data['description']);
                $coupon->setFromdate(strtotime($data['fromdate']));
                $coupon->setTodate(strtotime($data['todate']));
                $coupon->setIsdelete(0);
                $coupon->setReuse($data['reuse']);
                $this->modelCoupon->edit($coupon);


                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'coupon'));

            }

            return new ViewModel(array(
                'data' =>$coupon,
                'title' => $this->translator->translate('Edit coupon:')
            ));
        }
    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $menu = $this->modelCoupon->findOneBy(array('id'=>$id));
            $menu->setIsdelete(1);
            $this->modelCoupon->edit($menu);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function detailAction(){
        $id = $this->params()->fromRoute('id');
        $menuInfo = $this->modelMenu->findOneBy(array('id'=>$id));
        $dataRow = $this->modelMenu->convertSingleToArray($menuInfo);

        $data =  array(
            'title'=> 'Detail: '.$menuInfo->getName(),
            'link' => 'admin/index',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'sku' => 'Sku',
                'cost' => 'Cost',
                'name' => 'Name',
                'catId' => 'Category',
                'desc' => 'Desc',
//                'image' => 'Image',
            )
        );
        return new ViewModel(array('data' => $data ));
    }
}