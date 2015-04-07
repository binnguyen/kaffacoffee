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
    protected  $catModel;
    public function init(){
        parent::init();
        $this->catModel = new categoryModel($this->doctrineService);
    }
    public function indexAction(){

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
            'title' => $this->translator->translate('Category')));
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
    public function addAction(){
        $menuForm = new categoryForm();
        $id = $this->params()->fromRoute('id');
        //set form values

        if($id){
            $menu = $this->catModel->findOneBy(array('id'=>$id));

            $menuForm->get('id')->setValue($menu->getId());
            $menuForm->get('name')->setValue($menu->getName());
            $menuForm->setAttribute('action', '/admin/category/add/'.$id);

        }

        if($this->getRequest()->isPost())
        {
            $data = $this->params()->fromPost();

            //validate form, comming soon for form->getData
            //$menuForm->setData($data);
            //if($menuForm->isValid()){
            //$data = $menuForm->getData();

            $id = $data['id'];

            if($id)
            {

                $category = $this->catModel->findOneBy(array('id' => $id));
                $category->setName($data['name']);
                $this->catModel->edit($category);
                $this->flashMessenger()->addSuccessMessage($this->translator->translate("Update Success") );
                return $this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            }
            else
            {

                $category = new Categories();
                $category->setName($data['name']);
                $category->setIsdelete(0);
                $this->catModel->insert($category);

                $this->flashMessenger()->addSuccessMessage($this->translator->translate("Insert Success"));
                return  $this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            }
        }
        return new ViewModel(
            array('form' => $menuForm,
                'title' => $this->translator->translate('Add New Category')));
    }
    public function deleteAllAction(){
        if($this->getRequest()->isPost()){
            $data = $this->params()->fromPost('data');
            $data = json_decode($data);

            foreach($data as $item){
                $catModel = $this->catModel->findOneBy(array('id'=>$item));
                $catModel->setIsdelete(1);
                $this->catModel->edit($catModel);
            }
            die;
        }
        die;
    }
    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');
        if($id){
            $cat = $this->catModel->findOneBy(array('id'=>$id));
            $cat->setIsdelete(1);
            $this->catModel->edit($cat);
            echo 1;die;
        }
        $this->flashMessenger()->addSuccessMessage($this->translator->translate("Delete Success"));
        $this->redirect()->toRoute('admin/child',array('controller'=>'category'));

    }
}