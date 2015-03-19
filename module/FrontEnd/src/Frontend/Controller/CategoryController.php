<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Frontend\Controller;
use Admin\Entity\Categories;
use Admin\Entity\Table;
use Admin\Model\categoryModel;
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class CategoryController extends FrontEndController
{
    protected   $modelCategories;
    protected   $translator;
    public function init(){
        $this->modelCategories = new categoryModel($this->doctrineService);
    }
    public function indexAction()
    {
        $categories = $this->modelCategories->findBy(array('isdelete'=>'0'));
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelCategories->convertToArray($categories);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manage categories'),
            'link' => 'admin/category',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'name' => 'Name'
            ),
            'hideDeleteButton' => 1,
            'hideDetailButton' => 1
        );
        return new ViewModel(array('data'=>$data));
    }

}