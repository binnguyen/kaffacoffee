<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Payment;
use Admin\Model\paymentModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Form\paymentForm;
use Zend\Validator\File\Size;


class PaymentController extends BaseController
{
    protected   $modelCustomer;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $customerTable = $this->sm->get($service_locator_str);
        $this->modelCustomer = new paymentModel($customerTable);
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
        //end check login

        return parent::onDispatch($e);
    }

    public function ajaxListAction(){

        $fields = array(
            'id',
            'title',
            'value',
            'reason',
            'time',
            'categoryId',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn(4);
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = '';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }
        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\Payment c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql.$customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Payment c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();
        // map data
        $ret = array_map(function($item) {

            $paymentCate = Utility::getPaymentCateInfo($item->getCategoryId());
            // create link
            $linkEdit =   '/admin/payment/add/'.$item->getId() ;
            $linkDelete =  '/admin/payment/delete/'.$item->getId() ;
            $linkDetail =   '/admin/payment/detail/'.$item->getId() ;
            ($item->getTime() != '') ? $time = date('d-m-Y',$item->getTime()) : $time = time();
            return array(
                'id' => $item->getId(),
                'title' => $item->getTitle(),
                'value' => number_format($item->getValue()),
                'reason' => $item->getReason(),
                'time' => $time ,
                'categoryId' => $paymentCate->getName(),
                'action'=> '
                <a href="'.$linkDetail.'" class="btn btn-info"><i class="icon-edit-sign"></i></a>
                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a href="'.$linkDelete.'" class="btn btn-danger"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }


    public function indexAction()
    {
        return new ViewModel(array('title'=> $this->translator->translate('Payment')));
    }


    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');

        // check empty payment category

        if($id == ''){

            $customer = new Payment();
            $customerForm = new paymentForm();

            if($request->isPost()){
                $data = $this->params()->fromPost();
                if(!isset($data['categoryId']) || $data['categoryId'] == 0){

                    $this->flashMessenger()->addErrorMessage("You must select Category!!! ");
                    $this->redirect()->toRoute('admin/child',array('controller'=>'payment','action'=>'add'));

                }
                ($data['time'] == '') ? $time = time() : $time = strtotime($data['time']);
                $customer->setTitle($data['title']);
                $customer->setValue($data['value']);
                $customer->setReason($data['reason']);
                $customer->setTime($time);
                $customer->setCategoryId(($data['categoryId']));
                $this->modelCustomer->insert($customer);

            }
            return new ViewModel(array(
                'data' =>$customer,
                'title' => 'Edit accrued: '.$customer->getTitle(),
                'form' => $customerForm
            ));

        } else{

            $event = $this->modelCustomer->findOneBy(array('id'=>$id));
            $configForm = new paymentForm();
            $configForm->setAttribute('action', '/admin/payment/add/'.$id);
            $configForm->get('id')->setValue($event->getId());
            $configForm->get('title')->setValue($event->getTitle());
            $configForm->get('value')->setValue($event->getValue());
            $configForm->get('reason')->setValue($event->getReason());
            $configForm->get('time')->setValue(date("m-d-Y",$event->getTime()));


            if($request->isPost()){
                $data = $this->params()->fromPost();
                if(!isset($data['categoryId']) || $data['categoryId'] == 0){
                    $this->flashMessenger()->addErrorMessage("You must select Category!!! ");
                    $this->redirect()->toRoute('admin/child',array('controller'=>'payment','action'=>'add','id'=>$id));

                }
                $value = $event->getValue();
                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelCustomer->findOneBy(array('id'=>$idFormPost));
                $event->setTitle($data['title']);
                $event->setValue($data['value']);
                $event->setReason($data['reason']);
                $event->setTime(strtotime($data['time']));
                $this->modelCustomer->edit($event);

                //update form

                $configForm->get('title')->setValue($event->getTitle());
                $configForm->get('value')->setValue($event->getValue());
                $configForm->get('reason')->setValue($event->getReason());
                $configForm->get('time')->setValue(date("m-d-Y",$event->getTime()));
            }

            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit accrued: '.$event->getTitle(),
                'form' => $configForm
            ));

        }
    }


    public function deleteAction()
    {

        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $menu = $this->modelCustomer->findOneBy(array('id'=>$id));
            $this->modelCustomer->edit($menu);
            $this->modelCustomer->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }


}