<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Categories;
use Admin\Form;
use Admin\Form\categoryForm;
use Velacolib\Utility\Table;
use Velacolib\Utility\Table\AjaxTable;
use Velacolib\Utility\Table\Detail;
use Admin\Model\categoryModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;



class CategoryController extends AdminGlobalController
{
    protected   $catModel;
    protected  $translator;



    public function init(){
        parent::init();
        $this->catModel = new categoryModel($this->doctrineService);
    }


    public function indexAction()
    {

        //config table
        /////column for table
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>true, 'type' => 'number','name'=>'id' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1, 'search'=>true, 'type' => 'text','name'=>'name' ),
            array('title' =>'Action', 'db' => 'id','dt' => 2, 'search'=>true, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/category';
                    return '

                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                         <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';
                }
            )

        );
        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/category');
        $table->setTablePrefix('cat');
        $table->setExtendSQl(array(
            array('AND','cat.isdelete','=','0'),
        ));
        $table->setAjaxCall('/admin/category');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->catModel);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Category'))
        );
    }


    public function detailAction()
    {

        $id = $this->params()->fromRoute('id');
        $menuInfo = $this->catModel->findOneBy(array('id'=>$id));
        $dataRow = $this->catModel->convertSingleToArray($menuInfo);

        $column = array(
            array('title'=>'id','data'=>'id'),
            array('title'=>'name','data'=>'name'),
        );
        $detailTable = new Detail($column,$dataRow , 'admin/category');
        $detailTable->setDetailTitle($dataRow['name']);
        return new ViewModel(array('detailTable' => $detailTable,'title'=> $dataRow['name'] ));

    }



    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            if($request->isPost()) {
                $cat = new Categories();
                $cat->setName($this->params()->fromPost('name'));
                $cat->setIsdelete(0);
                $catInserted = $this->catModel->insert($cat);
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            return new ViewModel(array('title'=> $this->translator->translate('Add new category')));
        }
        else{

            $cat = $this->catModel->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->catModel->findOneBy(array('id'=>$idFormPost));
                $cat->setName($this->params()->fromPost('name'));
                $cat->setIsdelete(0);
                $this->catModel->edit($cat);
            }
            return new ViewModel(array(
                'data' =>$cat,
                'title' => $this->translator->translate('Edit category').': '.$cat->getName()
            ));
        }
    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
            $menu = $this->catModel->findOneBy(array('id'=>$id));
            $menu->setIsdelete(1);
            $this->catModel->edit($menu);
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