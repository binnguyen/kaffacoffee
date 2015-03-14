<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Table;
use Admin\Form\configForm;

use Admin\Model\configModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class ConfigController extends BaseController
{
    protected   $modelConfig;
    protected  $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $doctrine = $this->sm->get($service_locator_str);
        $this->modelConfig = new configModel($doctrine);
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

        return new ViewModel(array(
            'title'=>$this->translator->translate('Config'))
        );
    }

    public function ajaxListAction()
    {

        $fields = array(
            'id',
            'name',
            'value',
            'type',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
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
        $dql = "SELECT c FROM Admin\Entity\Config c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Config c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        $ret = array_map(function($item) {
            $linkEdit =   '/admin/config/add/'.$item->getId() ;
            $linkDelete =  '/admin/config/delete/'.$item->getId() ;
            $linkDetail =   '/admin/config/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'name' => $item->getName() ,
                'value' => $item->getValue() ,
                'type' => $item->getType() ,
                'action'=> '<a href="'.$linkDetail.'" class="btn btn-info"><i class="icon-edit-sign"></i></a><a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a><a href="'.$linkDelete.'" class="btn btn-danger"><i class="icon-trash"></i></a>'
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
        }
        else{
            $config = $this->modelConfig->findOneBy(array('id'=>$id));
            $configForm = new configForm();
            $configForm->setAttribute('action', '/admin/config/add/'.$id);
            $configForm->get('id')->setValue($config->getId());
            $configForm->get('name')->setValue($config->getName());
            $configForm->get('value')->setValue($config->getValue());
            $configForm->get('type')->setValue($config->getType());

            $configForm->add(
                array(
                    'type' => $config->getType(),
                    'name' => 'value',
                    'attributes' =>  array(
                        'id' => $config->getName(),
                        'value' => $config->getValue()
                    ),
                    'options' => array(
                        'label' => 'Value',
                    ),
                )
            );

            if($request->isPost()){
                $data = $this->params()->fromPost();
                $value = $config->getValue();
                if($data['type'] == 'file'){
                    $file = $this->params()->fromFiles();
                    $fileName = $file['value']['name'];
                    $fileTmp = $file['value']['tmp_name'];
                    $fileSize = $file['value']['size'];
                    $fileError = $file['value']['error'];
                    $fileType = $file['value']['type'];

                    if($fileName != ''){
                        move_uploaded_file($fileTmp, "./public/img/upload/config/".$fileName);
                        $value = "/img/upload/config/".$fileName;
                    }

                }else{
                    $value = $data['value'];
                }
//                echo '<pre>';
//                print_r($file);
//                print_r($data);
//                echo '</pre>';
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelConfig->findOneBy(array('id'=>$idFormPost));
                $cat->setValue($value);
                $this->modelConfig->edit($cat);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'config'));
            }
            return new ViewModel(array(
                'data' =>$config,
                'title' => 'Edit Config: '.$config->getName(),
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
            $combo = $this->modelCombo->findOneBy(array('id'=>$id));
            $combo->setIsdelete(1);
            $this->modelCombo->edit($combo);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {


    }

}