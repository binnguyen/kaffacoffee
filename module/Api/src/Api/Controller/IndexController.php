<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Api\Controller;
use Admin\Entity\Menu;
use Admin\Entity\Table;
use Admin\Model\categoryModel;
use Admin\Model\comboModel;
use Velacolib\Utility\Utility;
use Admin\Model\menuModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
class IndexController extends ApiController
{
    protected   $modelMenu;
    protected   $modelCombo;
    protected $catModel;
    public function init(){

        //check api
        $userApi = Utility::userApi(
            $this->params()->fromQuery('userName'),
            $this->params()->fromQuery('apiKey')
        );
        if($userApi->getId() == '')
            die(-1);
        $this->userId = $userApi->getId();
        //end check api

        $this->modelMenu = new menuModel($this->doctrineService);
        $this->modelCombo = new comboModel($this->doctrineService);
        $this->catModel = new categoryModel($this->doctrineService);
        $this->translator = Utility::translate();
        parent::init();
    }
    public function indexAction()
    {


        $menus = $this->modelMenu
            ->findBy(array('isdelete'=>'0'),
                array('id'=>'DESC')
            );
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelMenu->convertToArray($menus);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manage menu'),
            'link' => 'api/index',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'cost' => $this->translator->translate('Cost'),
                'name' => $this->translator->translate('Name'),
                'isCombo' => $this->translator->translate('Combo'),
                'catId' => $this->translator->translate('Category'),
                'desc' => $this->translator->translate('Desc'),
            ),
            'hideDeleteButton' => 1,
            'hideDetailButton' => 0,
            'hideEditButton' => 1,
        );
       return new ViewModel(array('data'=>$data));

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
            )
        );
        return new ViewModel(array('data' => $dataDetail));
    }
    public function pageAction(){
        $pageId = $this->params()->fromQuery('page');
        if($pageId == '')
            $pageId = 1;

        $query = array('obj.isdelete = 0 ');
        $cat = $this->params()->fromQuery('cat');
        if($cat != '')
            $query[] = 'obj.catId = '.$cat;

        $result = $this->modelMenu->paginator($query,array('orderBy'=>'id','order'=>'desc'),$pageId, $this->postPerPage);

        $menuData = $result['paginator'];
        $pageCount = $result['pageCount'];

        if($pageCount < $pageId){
            return -1;
        }
        $dataRow = $this->modelMenu->convertToArray($menuData);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manage menu'),
            'link' => 'api/index',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'cost' => $this->translator->translate('Cost'),
                'name' => $this->translator->translate('Name'),
                'isCombo' => $this->translator->translate('Combo'),
                'catId' => $this->translator->translate('Category'),
                'desc' => $this->translator->translate('Desc'),
            ),
            'hideDeleteButton' => 1,
            'hideDetailButton' => 0,
            'hideEditButton' => 1,
        );
        return new ViewModel(array('data'=>$data));

    }
    public function getCategoryAction(){
        $cat = $this->catModel
            ->findBy(array('isdelete'=>'0'),
                array('id'=>'DESC')
            );
        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->catModel->convertToArray($cat);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manage menu'),
            'link' => 'api/index',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'name' => $this->translator->translate('name'),
            ),
            'hideDeleteButton' => 1,
            'hideDetailButton' => 0,
            'hideEditButton' => 1,
        );
        return new ViewModel(array('data'=>$data));
    }
}