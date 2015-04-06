<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;

use Velacolib\Utility\Table;
use Velacolib\Utility\Table\AjaxTable;
use Velacolib\Utility\Table\Detail;

use Velacolib\Utility\Utility;
use Admin\Entity\Managetable;
use Admin\Model\tableModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class TableController extends AdminGlobalController
{
    protected   $modelTable;
    protected   $translator;

    public function init(){
        parent::init();
        $this->modelTable = new tableModel($this->doctrineService);
    }


    public function indexAction()
    {

        $columns = array(

            array('title' =>'ID', 'db' => 'id', 'dt' => 0,'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1, 'search'=>true, 'type' => 'text' ),

            array('title' =>'Action','db'=>'id','dt' => 2 , 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/table';
                    return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';

                }
            ),


        );


        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/table');
        $table->setTablePrefix('m');
        $table->setExtendSQl(array(
            array('AND','m.isdelete','=','0'),
        ));

        $table->setAjaxCall('/admin/table');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelTable);
        //end config table


        return new ViewModel(array(
            'table' => $table,
            'title' => $this->translator->translate('Manage Table')));
    }

    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            if($request->isPost()) {
                $table = new Managetable();
                $table->setName($this->params()->fromPost('name'));
                $table->setIsdelete(0);
                $tableInserted = $this->modelTable->insert($table);

                //flash
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'table'));
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'table'));
            return new ViewModel(array('title'=>$this->translator->translate('Add New Table')));
        }
        else{

            $table = $this->modelTable->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $idFormPost = $this->params()->fromPost('id');
                $table = $this->modelTable->findOneBy(array('id'=>$idFormPost));
                $table->setName($this->params()->fromPost('name'));
                $this->modelTable->edit($table);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update Success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'table'));
            }
            return new ViewModel(array(
                'data' =>$table,
                'title' => $this->translator->translate('Edit Table').': '.$table->getName()
            ));
        }
    }

    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $table = $this->modelTable->findOneBy(array('id'=>$id));
            $table->setIsdelete(1);
            $this->modelTable->edit($table);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }
    public function editAction()
    {


    }

}