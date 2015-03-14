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
use Zend\Mvc\Controller\AbstractActionController;


class IndexController extends AbstractActionController
{
    protected   $modelMenu;
    protected   $modelCombo;
    protected   $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $CategoriesTable = $this->sm->get($service_locator_str);
        $this->modelMenu = new menuModel($CategoriesTable);
        $this->modelCombo = new comboModel($CategoriesTable);
        $this->translator = Utility::translate();

        //check login
        $user = Utility::checkLogin($this);
        if(! is_object($user) && $user == 0){
            $this->redirect()->toRoute('frontend/child',array('controller'=>'login'));
        }

        return parent::onDispatch($e);
    }
    public function indexAction()
    {
        $menus = $this->modelMenu->findBy(array('isdelete'=>'0'));


        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelMenu->convertToArray($menus);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manage menu'),
            'link' => 'frontend/index',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'cost' => $this->translator->translate('Cost'),
                'name' => $this->translator->translate('Name'),
                'isCombo' => $this->translator->translate('Combo'),
                'catId' => $this->translator->translate('Category'),
                'desc' => $this->translator->translate('Desc'),
//                'image' => 'Image',
            ),
            'hideDeleteButton' => 1,
            'hideDetailButton' => 0,
            'hideEditButton' => 1,
        );
        return new ViewModel(array('data'=>$data, 'title' => $this->translator->translate('Menu')));
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

        $menusCombo = $this->modelCombo->findBy(array('isdelete'=>'0','menuParentId'=> $id));

        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelCombo->convertToArray($menusCombo);
        $dataChild =  array(
            'tableTitle'=> $this->translator->translate('Manage child combo'),
            'link' => 'admin/combo',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'menu_parent_id' => $this->translator->translate('Menu parent id'),
                'menu_child_id' => $this->translator->translate('Menu child id'),
                'menu_cost' => $this->translator->translate('Cost'),
                'menu_ta_cost' => $this->translator->translate('Take away'),
                'menu_quantity' => $this->translator->translate('Quantity'),
                'menu_total_cost' => $this->translator->translate('Total cost'),
                'menu_total_ta_cost' => $this->translator->translate('Total take away cost'),
            ),
            'hideDetailButton' => 1,
            'hideDeleteButton' => 1,
            'hideEditButton' => 1,
        );

        return new ViewModel(array('data' => $dataDetail,'dataChild'=>$dataChild));
    }
}