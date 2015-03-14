<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Customer;
use Admin\Model\customerModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Form\customerForm;
use Zend\Validator\File\Size;

class CustomerController extends AbstractActionController
{
    protected   $modelCustomer;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $customerTable = $this->sm->get($service_locator_str);
        $this->modelCustomer = new customerModel($customerTable);
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
        $categories = $this->modelCustomer->findBy(array('isdelete'=>'0'));
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelCustomer->convertToArray($categories);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manage customer'),
            'link' => 'admin/customer',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'fullname' => $this->translator->translate('Full name') ,
                'customerCode' => $this->translator->translate('Customer code') ,
                'level' => $this->translator->translate('Level') ,
                'phone' => $this->translator->translate('Phone') ,
                'email' => $this->translator->translate('Email') ,
                'address' => $this->translator->translate('Address') ,
                'birthday' => $this->translator->translate('Birthday') ,
                'image' => $this->translator->translate('Avatar') ,
            ),
            'hideDetailButton' => 1
        );
        return new ViewModel(array('data'=>$data,'title'=> $this->translator->translate('Customer')));
    }


    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');

        if($id == ''){

            $customer = new Customer();
            $customerForm = new customerForm();

            if($request->isPost()){
                $data = $this->params()->fromPost();
                $file = $request->getFiles()->toArray();

                $size = new Size(array('max'=>2000000)); //minimum bytes filesize


               $fileUpload =  Utility::uploadFile($file);
               $data['avatar'] = $fileUpload['avatar'];
               if(!$fileUpload['status']){
                   $customerForm->setMessages($fileUpload['error']);
               }

                $customer->setFullname($data['fullname']);
                $customer->setNicename($data['fullname']);
                $customer->setCustomerCode($data['customerCode']);
                $customer->setLevel(1);
                $customer->setPhone($data['phone']);
                $customer->setEmail($data['email']);
                $customer->setAddress($data['address']);
                $customer->setBirthday($data['birthday']);
                $customer->setAvatar($data['avatar']);
                $this->modelCustomer->insert($customer);

            }
            return new ViewModel(array(
                'data' =>$customer,
                'title' => 'Add customer: '.$customer->getFullname(),
                'form' => $customerForm
            ));

        } else{

            $event = $this->modelCustomer->findOneBy(array('id'=>$id));
            $configForm = new customerForm();
            $configForm->setAttribute('action', '/admin/customer/add/'.$id);
            $configForm->get('id')->setValue($event->getId());
            $configForm->get('fullname')->setValue($event->getFullname());
            $configForm->get('nicename')->setValue($event->getNicename());
            $configForm->get('customerCode')->setValue($event->getCustomerCode());
            $configForm->get('level')->setValue($event->getLevel());
            $configForm->get('phone')->setValue($event->getPhone());
            $configForm->get('email')->setValue($event->getEmail());
            $configForm->get('address')->setValue($event->getAddress());
            $configForm->get('birthday')->setValue($event->getBirthday());
            $configForm->get('avatar')->setValue('');
            $configForm->get('avatar_old')->setValue($event->getAvatar());


            if($request->isPost()){
                $file = $request->getFiles()->toArray();

                $data = $this->params()->fromPost();
                if(empty($file['avatar']['name'])){
                    $data['avatar'] = $data['avatar_old'];
                }else{
                    $fileUpload = Utility::uploadFile($file)  ;

                    $data['avatar'] = $fileUpload['avatar'];
                }
               // $value = $event->getValue();
                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelCustomer->findOneBy(array('id'=>$idFormPost));
                $event->setFullname($data['fullname']);
                $event->setNicename($data['fullname']);
                $event->setCustomerCode($data['customerCode']);
                $event->setLevel($data['level']);
                $event->setPhone($data['phone']);
                $event->setEmail($data['email']);
                $event->setAddress($data['address']);
                $event->setBirthday($data['birthday']);
                $event->setAvatar($data['avatar']);
                $this->modelCustomer->edit($event);

                //update form

                $configForm->get('fullname')->setValue($event->getFullname());
                $configForm->get('nicename')->setValue($event->getNicename());
                $configForm->get('customerCode')->setValue($event->getCustomerCode());
                $configForm->get('level')->setValue($event->getLevel());
                $configForm->get('phone')->setValue($event->getPhone());
                $configForm->get('email')->setValue($event->getEmail());
                $configForm->get('address')->setValue($event->getAddress());
                $configForm->get('birthday')->setValue($event->getBirthday());
                $configForm->get('avatar')->setValue($event->getAvatar());
            }
            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit customer: '.$event->getFullname(),
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
        //get user by id
        $id = $this->params()->fromRoute('id');
        $user = $this->model->findOneBy(array('id'=>$id));
        $user->setFullName('tri 1234');
        $this->model->edit($user);
        //update user

    }

}