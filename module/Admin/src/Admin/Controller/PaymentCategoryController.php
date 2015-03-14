<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Categories;
use Admin\Entity\PaymentCategory;
use Admin\Form\paymentCategoryForm;
use Admin\Model\categoryModel;
use Admin\Model\paymentCategoryModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Admin\Entity\Table;
use Zend\Mvc\Controller\AbstractActionController;



class PaymentCategoryController extends BaseController
{
    protected   $modelCategories;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $CategoriesTable = $this->sm->get($service_locator_str);
        $this->modelCategories = new paymentCategoryModel($CategoriesTable);
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



    public function indexAction()
    {
        return new ViewModel(array('title'=> $this->translator->translate('Payment category')));
    }



    public function ajaxListAction()
    {

        $fields = array(
            'id',
            'name',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();

        // WHERE conditions
        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search);

        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\PaymentCategory c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\PaymentCategory c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        $ret = array_map(function($item) {
            $linkEdit =   '/admin/Payment-category/add/'.$item->getId() ;
            $linkDelete =  '/admin/Payment-category/delete/'.$item->getId() ;
            $linkDetail =   '/admin/Payment-category/detail/'.$item->getId() ;

            return array(
                'id' => $item->getId(),
                'name' => $item->getName() ,
                'actions'=> '<a href="'.$linkDetail.'" class="btn btn-info"><i class="icon-edit-sign"></i></a><a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a><a href="'.$linkDelete.'" class="btn btn-danger"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }

    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');

        if($id == ''){

            $customer = new PaymentCategory();
            $customerForm = new paymentCategoryForm();

            if($request->isPost()){
                $data = $this->params()->fromPost();

                $customer->setName($data['name']);
                $customer->setIsdelete(0);

                $this->modelCategories->insert($customer);

            }
            return new ViewModel(array(
                'data' =>$customer,
                'title' => 'Edit accrued: '.$customer->getName(),
                'form' => $customerForm
            ));

        } else{

            $event = $this->modelCategories->findOneBy(array('id'=>$id));
            $configForm = new paymentCategoryForm();
            $configForm->setAttribute('action', '/admin/payment-category/add/'.$id);
            $configForm->get('id')->setValue($event->getId());
            $configForm->get('name')->setValue($event->getName());


            if($request->isPost()){

                $data = $this->params()->fromPost();
                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelCategories->findOneBy(array('id'=>$idFormPost));
                $event->setName($data['name']);
                $event->setIsdelete(0);
                $this->modelCategories->edit($event);
                //update form

                $configForm->get('name')->setValue($event->getName());
                $this->flashMessenger()->addSuccessMessage("Save change...");
            }

            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit accrued: '.$event->getName(),
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
            $menu = $this->modelCategories->findOneBy(array('id'=>$id));
            $menu->setIsdelete(1);
            $this->modelCategories->edit($menu);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }


    public function editAction()
    {


    }

}