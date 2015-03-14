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
use Admin\Entity\Table;
use Velacolib\Utility\Utility;
use Admin\Model\couponModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class CouponController extends BaseController
{
    protected   $modelCoupon;
    protected   $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrineService = $this->sm->get($service_locator_str);
        $this->modelCoupon = new couponModel($doctrineService);
        $this->translator = Utility::translate();

        //check login
        $user = Utility::checkLogin($this);
        if(! is_object($user) && $user == 0){
            $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }else{
            $isPermission = Utility::checkRole($user->userType,ROLE_ADMIN);
            if( $isPermission == false)
                $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }

        return parent::onDispatch($e);
    }


    public function indexAction()
    {

        return new ViewModel(array('title'=>$this->translator->translate('Coupon')));
    }


    public function ajaxListAction(){

        $fields = array(
            'id',
            'code',
            'value',
            'fromdate',
            'todate',
            'type',
            'description',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = ' c.isdelete = 0';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }
        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\Coupon c ";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Coupon c ";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();
        // map data
        $ret = array_map(function($item) {
            // create link
            $linkEdit =   '/admin/coupon/add/'.$item->getId() ;
            $linkDelete =  '/admin/coupon/delete/'.$item->getId() ;
            $linkDetail =   '/admin/coupon/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'code' => $item->getCode(),
                'value' => $item->getValue(),
                'fromdate' => date('d-m-Y',$item->getFromDate()),
                'todate' => date('d-m-Y',$item->getToDate()),
                'type' => $item->getType(),
                'description' => $item->getDescription(),
                'action'=> '
                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete" ><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

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

            return new ViewModel(array('title'=> $this->translator->translate('Add new coupon')));
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