<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Frontend\Controller;
use Admin\Entity\Menu;
use Admin\Entity\Table;
use Admin\Model\comboModel;
use Velacolib\Utility\Utility;
use Admin\Model\menuModel;
use Zend\View\Model\ViewModel;
use Velacolib\Utility\Table\AjaxTable;
use Zend\Mvc\Controller\AbstractActionController;


class IndexController extends FrontEndController
{
    protected   $modelMenu;
    protected   $modelCombo;
    protected   $translator;
    public function init(){
        parent::init();
        $this->modelMenu = new menuModel($this->doctrineService);
        $this->translator = Utility::translate();

    }

    public function indexAction()
    {
        $columns = array(
            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Category', 'db' => 'catId','dt' => 2, 'search'=>false, 'type' => 'number',
                'dataSelect' => Utility::getCategoryForSelect()
            ),
            array('title' =>'Cost', 'db' => 'cost','dt' => 3, 'search'=>false, 'type' => 'number',
                'formatter' => function($d, $row){
                    return Utility::formatCost($d);
                }
            ),
            array('title' =>'Take Away Cost', 'db' => 'taCost','dt' => 4, 'search'=>false, 'type' => 'number','formatter' => function($d, $row){
                return Utility::formatCost($d);
            } ),

        );

        /////end column for table
        $table = new AjaxTable($columns, array(), 'frontend/index');
        $table->setTablePrefix('m');
        $table->setExtendSQl(array(
            array('AND','m.isdelete','=','0'),
        ));
        $table->setAjaxCall('/frontend/index');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelMenu);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Menu')));

    }

    public function detailAction(){
        $id = $this->params()->fromRoute('id');
        $menuInfo = $this->modelMenu->findOneBy(array('id'=>$id));
        $dataRow = $this->modelMenu->convertSingleToArray($menuInfo);

        $dataDetail =  array(
            'title'=> $this->translator->translate('Detail').': '.$menuInfo->getName(),
            'link' => 'admin/index',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'cost' => $this->translator->translate('Cost'),
                'taCost' => $this->translator->translate('Take away'),
                'name' => $this->translator->translate('Name'),
                'catId' => $this->translator->translate('Category'),
                'desc' => $this->translator->translate('Desc'),
//                'image' => 'Image',
            )
        );


        return new ViewModel(array('data' => $dataDetail));
    }
}