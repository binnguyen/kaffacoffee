<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Event;
use Admin\Entity\Table;
use Admin\Form\configForm;

use Admin\Form\eventForm;
use Admin\Model\configModel;
use Admin\Model\eventModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class EventController extends AbstractActionController
{
    protected   $modelEvent;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelEvent = new eventModel($doctrine);
        $this->translator =  Utility::translate();

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
        $events = $this->modelEvent->findBy(array('isdelete'=>0));
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelEvent->convertToArray($events);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Event'),
            'link' => 'admin/event',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'name' => $this->translator->translate('Name'),
                'value' => $this->translator->translate('Value'),
                'type' => $this->translator->translate('Type'),
            ),
            'hideDeleteButton' => 0,
            'hideDetailButton' => 1

        );
        return new ViewModel(array('data'=>$data,
            'title'=>$this->translator->translate('Event')));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            $event = new Event();
            $configForm = new eventForm();
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $event->setName($data['name']);
                $event->setValue($data['value']);
                $event->setType($data['type']);
                $event->getIsdelete(0);
                $this->modelEvent->insert($event);
            }

            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit event: '.$event->getName(),
                'form' => $configForm
            ));
        }
        else{

            $event = $this->modelEvent->findOneBy(array('id'=>$id));
            $configForm = new eventForm();
            $configForm->setAttribute('action', '/admin/event/add/'.$id);
            $configForm->get('id')->setValue($event->getId());
            $configForm->get('name')->setValue($event->getName());
            $configForm->get('value')->setValue($event->getValue());
            $configForm->get('type')->setValue($event->getType());


            if($request->isPost()){
                $data = $this->params()->fromPost();
                $value = $event->getValue();
                $idFormPost = $this->params()->fromPost('id');
                $event = $this->modelEvent->findOneBy(array('id'=>$idFormPost));
                $event->setType($data['type']);
                $event->setValue($data['value']);
                $event->setName($data['name']);
                $this->modelEvent->edit($event);

                //update form
                $configForm->get('name')->setValue($event->getName());
                $configForm->get('value')->setValue($event->getValue());
                $configForm->get('type')->setValue($event->getType());

            }
            return new ViewModel(array(
                'data' =>$event,
                'title' => 'Edit event: '.$event->getName(),
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
            $event = $this->modelEvent->findOneBy(array('id'=>$id));
            $event->setIsdelete(1);
            $this->modelEvent->edit($event);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {


    }

}