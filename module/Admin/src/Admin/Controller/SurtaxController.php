<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/4/14
 * Time: 9:43 AM
 */

namespace Admin\Controller;
use Admin\Entity\Surtax;
use Admin\Model\surTaxModel;
use Velacolib\Utility\Table\AjaxTable;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;



class SurtaxController extends AdminGlobalController {


    protected   $modelSurTax;
    protected  $translator;
    public function init(){
        $this->modelSurTax = new surTaxModel($this->doctrineService);
    }

    public function indexAction(){
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name', 'dt' => 1, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Value', 'db' => 'value', 'dt' => 2, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Type', 'db' => 'type', 'dt' => 3, 'search'=>false, 'type' => 'number' ),
        );

        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/surtax');
        $table->setTablePrefix('ts');
        $table->setAjaxCall('/admin/surtax');
        $this->tableAjaxRequest($table,$columns,$this->modelSurTax);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Manage Surtax')));
    }

    public function addAction(){
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            if($request->isPost()) {
                $data = $this->params()->fromPost();
                $surtax = new Surtax();
                $surtax->setName($data['name']);
                $surtax->setValue($data['value']);
                $surtax->setType($data['type']);
                $this->modelSurTax->insert($surtax);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'surtax'));
            }
            //insert new user

            return new ViewModel(array('title'=> $this->translator->translate('Add Surtax')));
        }
        else{

            $surtax = $this->modelSurTax->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $data = $this->params()->fromPost();
                $idFormPost = $data['id'];
                $surtax = $this->modelSurTax->findOneBy(array('id'=>$idFormPost));
                $surtax->setName($data['name']);
                $surtax->setValue($data['value']);
                $surtax->setType($data['type']);
                $this->modelSurTax->edit($surtax);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'surtax'));
            }

            return new ViewModel(array(
                'data' =>$surtax,
                'title' => $this->translator->translate('Edit Surtax:')
            ));
        }
    }

    public function detail(){

    }

    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->params()->fromPost('id');
//            $menu = $this->modelSurTax->findOneBy(array('id' => $id));
//            $menu->setIsdelete(1);
//            $this->modelSurTax->edit($menu);
            $this->modelSurTax->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
} 