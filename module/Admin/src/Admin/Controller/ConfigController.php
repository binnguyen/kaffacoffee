<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\ItemUnit;
use Admin\Entity\ItemUnitConvert;
use Admin\Entity\Table;
use Admin\Form\configForm;
use Admin\Form\itemUnitConvertForm;
use Admin\Model\unitConvertModel;
use Velacolib\Utility\Table\AjaxTable;
use Admin\Form\itemUnitForm;
use Admin\Model\configModel;
use Admin\Model\unitModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class ConfigController extends AdminGlobalController
{
    protected   $modelConfig;
    protected  $translator;
    protected $modelUnit;
    protected $modelConvertUnit;
    public function init(){
        $this->modelConfig = new configModel($this->doctrineService);
        $this->modelUnit = new unitModel($this->doctrineService);
        $this->modelConvertUnit = new unitConvertModel($this->doctrineService);
    }
    public function indexAction()
    {
        $combos = $this->modelConfig->findAll();
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelConfig->convertToArray($combos);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Config'),
            'link' => 'admin/config',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'name' => $this->translator->translate('Name'),
                'value' => $this->translator->translate('Value'),

            ),
            'hideDeleteButton' => 1,
            'hideDetailButton' => 1

        );

        return new ViewModel(array('data'=>$data,
            'title'=>$this->translator->translate('Manage Config')));
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
            // $configForm->get('value')->setValue($config->getValue());
            $configForm->get('type')->setValue($config->getType());

            $configType = $config->getType();
            $configValueImg = '';
            if($configType == 'file'){
                $configValueImg = '<img style="width:100px" src="'.$config->getValue().'">';
            }
            if($config->getName() != 'emailPassword'){
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
            }

            if($config->getName() == 'currency_before'){
                $configForm->add(
                    array(
                        'type' => 'Zend\Form\Element\Select',
                        'name' => 'value',
                        'attributes' =>  array(
                            'id' => $config->getName(),
                            'value' => $config->getValue(),
                            'options' => array(
                                0 =>'After',
                                1 => 'Before',
                            ),
                        ),
                        'options' => array(

                        ),
                    )
                );
            }

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
                'title' => 'Edit Config: '.$this->translator->translate($config->getName()),
                'form' => $configForm,
                'configValueImg' => $configValueImg
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
    //unit
    public function unitListAction(){
        //config table
        /////column for table
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>true, 'type' => 'number','name'=>'id' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1, 'search'=>true, 'type' => 'text','name'=>'name' ),
            array('title' =>'Converted', 'db' => 'converted','dt' => 2, 'search'=>true, 'type' => 'text','name'=>'name','formatter'=> function($d,$row){
                $list = '';
                $arr = json_decode($d);
                foreach($arr as $k => $item){
                    $list .= '<li>'.$item.' '.$k.'</li>';
                }
                $ul = '<ul>'.$list.'</ul>';

                return $ul ;
            } ),
            array('title' =>'Action', 'db' => 'id','dt' => 3, 'search'=>true, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/config/additemunit';
                    $actionCovertUrl = '/admin/config/additemunitconvert';
                    $actionDeleteUrl = '/admin/config/deleteitemunit';
                    return '

                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/'.$d.'"><i class="icon-edit"></i></a>
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionCovertUrl.'/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-specific-link="1" data-link="'.$actionDeleteUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';
                }
            )
        );
        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/config/unitlist');
        $table->setTablePrefix('u');
        $table->setAjaxCall('/admin/config/unitlist');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelUnit);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Unit')));
    }
    public function addItemUnitAction(){
        $menuForm = new itemUnitForm();
        $id = $this->params()->fromRoute('id');
        //set form values

        if($id){
            $unit = $this->modelUnit->findOneBy(array('id'=>$id));
            $menuForm->get('id')->setValue($unit->getId());
            $menuForm->get('name')->setValue($unit->getName());
            $menuForm->setAttribute('action', '/admin/config/additemunit/'.$id);
        }
        if($this->getRequest()->isPost())
        {
            $data = $this->params()->fromPost();
            $id = $data['id'];

            if(!$id)
            {
                $unit = new ItemUnit();
                $unit->setName($data['name']);
                $unit->setShortName($data['short_name']);

                $this->modelUnit->insert($unit);
                $this->flashMessenger()->addSuccessMessage($this->translator->translate("Insert Success"));
                return  $this->redirect()->toRoute('admin/child',array('controller'=>'config','action'=>'unitlist'));
            }
        }
        return new ViewModel(
            array('form' => $menuForm,
                'title' => $this->translator->translate('Add New Unit')));

    }
    public function deleteItemUnitAction(){
        $id = $this->params()->fromPost('id');
        if($id){
            $this->modelUnit->delete(array('id'=>$id));
            $this->modelConvertUnit->delete(array('item_unit_one'=>$id));
            echo 1;
            die;
        }
        $this->flashMessenger()->addSuccessMessage($this->translator->translate("Delete Success"));
        $this->redirect()->toRoute('admin/child',array('controller'=>'category'));
    }
    //unit convert
    public function unitListCovertAction(){
        //config table
        /////column for table
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>true, 'type' => 'number','name'=>'id' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1, 'search'=>true, 'type' => 'text','name'=>'name' ),
            array('title' =>'Action', 'db' => 'id','dt' => 2, 'search'=>true, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/config/additemunit';
                    $actionDeleteUrl = '/admin/config/deleteitemunit';
                    return '

                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-specific-link="1" data-link="'.$actionDeleteUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';
                }
            )
        );
        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/config/unitlist');
        $table->setTablePrefix('u');
        $table->setAjaxCall('/admin/config/unitlist');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelUnit);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Unit')));
    }
    public function addItemUnitConvertAction(){
        $menuConveryForm = new itemUnitConvertForm();
        $id = $this->params()->fromRoute('id');
        //set form values

        if($id){
            $menuConveryForm->get('unit_item_one')->setValue($id);
            $menuConveryForm->setAttribute('action', '/admin/config/additemunitconvert/'.$id);
        }
        if($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $modelCovertUnit = new ItemUnitConvert();

            $modelCovertUnit->setUnitItemOne($data['unit_item_one']);
            $modelCovertUnit->setUnitItemTwo($data['unit_item_two']);
            $modelCovertUnit->setValue($data['value']);
            $this->modelConvertUnit->insert($modelCovertUnit);

            $modelCovertUnit = new ItemUnitConvert();
            $modelCovertUnit->setUnitItemOne($data['unit_item_two']);
            $modelCovertUnit->setUnitItemTwo($data['unit_item_one']);
            $modelCovertUnit->setValue(1/$data['value']);
            $this->modelConvertUnit->insert($modelCovertUnit);

        }
        return new ViewModel(
            array('form' => $menuConveryForm,
                'title' => $this->translator->translate('Add New Unit')));

    }
    public function deleteItemUnitConvertAction(){
        $id = $this->params()->fromPost('id');
        if($id){
            $this->modelUnit->delete(array('id'=>$id));
            echo 1;die;
        }
        $this->flashMessenger()->addSuccessMessage($this->translator->translate("Delete Success"));
        $this->redirect()->toRoute('admin/child',array('controller'=>'category'));
    }

}