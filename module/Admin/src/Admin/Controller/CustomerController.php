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
use Velacolib\Utility\Table\AjaxTable;

class CustomerController extends AdminGlobalController
{
    protected   $modelCustomer;
    protected  $translator;

    public function init(){

        parent::init();
        $this->modelCustomer = new customerModel($this->doctrineService);
    }


    public function indexAction()
    {
        //config table
        /////column for table
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Full name', 'db' => 'fullname','dt' => 1, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Nice name', 'db' => 'nicename','dt' => 2, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Customer Code', 'db' => 'customerCode','dt' => 3, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Level', 'db' => 'level','dt' => 4, 'search'=>true, 'type' => 'number' ),
            array('title' =>'Phone', 'db' => 'phone','dt' => 5, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Email', 'db' => 'email','dt' => 6, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Address', 'db' => 'address','dt' => 7, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Birthday', 'db' => 'birthday','dt' => 8, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Avatar', 'db' => 'avatar','dt' => 9, 'search'=>true, 'type' => 'text',
                'formatter' =>function($d,$row){
                $image = '<img src="'.$d.'" width="50" />';
                    return $image;
            } ),
            array('title' =>'Action', 'db' => 'id','dt' => 10, 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/customer';
                    return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a class="btn-xs action action-detail btn btn-danger  " href="'.$actionUrl.'/delete/'.$d.'"><i class="icon-remove"></i></a>
                    ';
                }
            )

        );

        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/customer');
        $table->setTablePrefix('m');
        $table->setExtendSQl(array(
            array('AND','m.isdelete','=','0'),
        ));
        $table->setAjaxCall('/admin/customer');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelCustomer);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Manager Customer')));
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
                    print_r($customerForm->getMessages());die;
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
                'title' => 'Add customer: ',
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
                    if(!$fileUpload['status']){
                      //  $configForm->setMessages($fileUpload['error']);
                        print_r($fileUpload['error']);die;

                    }else{
                    }

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