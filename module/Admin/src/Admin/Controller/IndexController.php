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

use Admin\Entity\Menu;
use Admin\Entity\MenuCombo;
use Admin\Entity\MenuItem;
use Admin\Entity\Managetable;

use Admin\Model\comboModel;
use Admin\Model\couponModel;
use Velacolib\Utility\Utility;
use Admin\Model\menuModel;
use Zend\View\Model\ViewModel;
use Admin\Model\menuItemModel;
use Zend\Mvc\Controller\AbstractActionController;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;



class IndexController extends AdminGlobalController
{
    protected $modelMenu;
    protected $modelCombo;
    protected $translator;
    protected $modelMenuItem;

//

    public function init(){
        parent::init();
        $this->modelMenu = new menuModel($this->doctrineService);
        $this->modelCombo = new comboModel($this->doctrineService);
        $this->modelMenuItem = new  menuItemModel($this->doctrineService);
    }

    public function indexAction()
    {

        //config table
        /////column for table
        $columns = array(

            array('title' =>'Id', 'db' => 'id', 'dt' => 0, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Name', 'db' => 'name','dt' => 1, 'search'=>true, 'type' => 'text' ),
            array('title' =>'Category', 'db' => 'catId','dt' => 2, 'search'=>false, 'type' => 'number',
                'dataSelect' => Utility::getCategoryForSelect()
            ),
            array('title' =>'Cost', 'db' => 'cost','dt' => 3, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Take Away Cost', 'db' => 'taCost','dt' => 4, 'search'=>false, 'type' => 'number' ),
            array('title' =>'Action','db'=>'id','dt' => 5, 'search'=>false, 'type' => 'number',
                'formatter' => function( $d, $row ) {
                    $actionUrl = '/admin/index';
                    return '
                        <a class="btn-xs action action-detail btn btn-success btn-default" href="'.$actionUrl.'/add/'.$d.'"><i class="icon-edit"></i></a>
                        <a data-id="'.$d.'" id="'.$d.'" data-link="'.$actionUrl.'" class="btn-xs action action-detail btn btn-danger  btn-delete " href="javascript:void(0)"><i class="icon-remove"></i></a>
                    ';

                }
            ),


        );


        /////end column for table
        $table = new AjaxTable($columns, array(), 'admin/index');
        $table->setTablePrefix('m');
        $table->setExtendSQl(array(
            array('AND','m.isdelete','=','0'),
        ));
        $table->setAjaxCall('/admin/index');
        $table->setActionDeleteAll('deleteall');
        $this->tableAjaxRequest($table,$columns,$this->modelMenu);
        //end config table
        return new ViewModel(array('table' => $table,
            'title' => $this->translator->translate('Manage Menu')));

    }


    public function ajaxListAction()
    {

        $fields = array(
            'id',
            'name',
            'cost',
            'takeAwayCost',
            'description',
            'catId',
            'isdelete',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = ' c.isdelete = 0';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }


        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);


        // DQL
        $dql = "SELECT c FROM Admin\Entity\Menu c  ";



        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customQuery  . $dqlWhere . $dqlOrder);

        //$parameter = array('isdelete'=>0);

        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }

        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();
       // echo $query->getSQL();


        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Menu c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();

        $ret = array_map(function($item) {
            $categoryInfo = Utility::getCatInfo($item->getCatId());
           $formular =  Utility::getMenuStoreInMenu($item->getId());

            $linkEdit =   '/admin/index/add/'.$item->getId() ;
            $linkDelete =  '/admin/index/delete/'.$item->getId() ;
            $linkDetail =   '/admin/index/detail/'.$item->getId() ;

            return array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'cost'=>$item->getCost(),
                'takeAwayCost'=>$item->getTakeAWayCost(),
                'description'=>$item->getDescription(),
                'catId'=>$categoryInfo->getName(),
                'formula'=>$formular,
                'action'=> '
                <a href="'.$linkDetail.'" class="btn btn-info"><i class="icon-edit-sign"></i></a>
                <a class="btn btn-primary" href="'.$linkEdit.'"><i class="icon-edit-sign"></i></a>
                <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="#"  class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }


    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if ($id == '') {
            if ($request->isPost()) {

                $menu = new Menu();
                $menu->setName($this->params()->fromPost('name'));
                $menu->setCost($this->params()->fromPost('cost'));
                $menu->setTakeAwayCost($this->params()->fromPost('tacost'));
                $menu->setDescription($this->params()->fromPost('desc'));
                $menu->setCatId($this->params()->fromPost('cat_id'));
                $menu->setIsCombo($this->params()->fromPost('is_combo'));
                $menu->setIsdelete(0);
                $menu->setImage(' ');
                $comboInserted = $this->modelMenu->insert($menu);
                if ($this->params()->fromPost('is_combo') == 1) {
                    $combos = $this->params()->fromPost('detail');
                    foreach ($combos as $combo) {
                        $this->addNewCombo($combo, $comboInserted->getId());
                    }
                }
                $menuItem = $this->params()->fromPost('item');
                if ($menuItem != '') {
                    foreach ($menuItem as $item) {
                        $this->addNewMenuItem($item, $comboInserted->getId());
                    }
                }
                $this->flashMessenger()->addSuccessMessage("Insert success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'index'));
            }

            //insert new user
            return new ViewModel(array('title' => $this->translator->translate('Add New Menu')));
        }
        else
        {
            //edit combo menu:
            //
            //cache 1: edit menu check is_combo if(is_combo) = 1 delete all old combo, add new combo
            /////////// that's right but order has been changed by new edited combo => order not right


            //cache 2: edit menu check is_combo if(is_combo) = 1 set old menu isDelete = 1 then add new menu combo
            /////////// that's right, that's older menu + combo deleted. order table can see them BUT user not see old combo, Order reference menu combo not change.

            $menu = $this->modelMenu->findOneBy(array('id' => $id));
            if ($request->isPost()) {
                if ($this->params()->fromPost('is_combo') == 0) {
                    //edit menu
                    $idFormPost = $this->params()->fromPost('id');
                    $menu = $this->modelMenu->findOneBy(array('id' => $idFormPost));

                    $menu->setName($this->params()->fromPost('name'));
                    $menu->setCost($this->params()->fromPost('cost'));
                    $menu->setTakeAwayCost($this->params()->fromPost('tacost'));
                    $menu->setDescription($this->params()->fromPost('desc'));
                    $menu->setCatId($this->params()->fromPost('cat_id'));
                    $menu->setIsCombo($this->params()->fromPost('is_combo'));
                    $menu->setImage(' ');
                    $menu->setIsdelete(0);
                    $this->modelMenu->edit($menu);
                    //end edit menu
                    $this->modelMenuItem->deleteAll(array('menuId' => $id));

                    $menuItem = $this->params()->fromPost('item');
                    if ($menuItem != '') {
                        foreach ($menuItem as $item) {
                            $this->addNewMenuItem($item, $id);
                        }
                    }

                } else {
                    //edit combo in menu
                    if ($this->params()->fromPost('is_combo') == 1) {
                        //hide old menu
                        $idFormPost = $this->params()->fromPost('id');
                        $menuOlder = $this->modelMenu->findOneBy(array('id' => $idFormPost));
                        $menuOlder->setIsdelete(1);
                        $this->modelMenu->edit($menu);

                        //new menu
                        $menu = new Menu();
                        $menu->setName($this->params()->fromPost('name'));
                        $menu->setCost($this->params()->fromPost('cost'));
                        $menu->setTakeAwayCost($this->params()->fromPost('tacost'));
                        $menu->setDescription($this->params()->fromPost('desc'));
                        $menu->setCatId($this->params()->fromPost('cat_id'));
                        $menu->setIsCombo($this->params()->fromPost('is_combo'));
                        $menu->setImage('');
                        $menu->setIsdelete(0);
                        $newMenuInserted = $this->modelMenu->insert($menu);

                        $combos = $this->params()->fromPost('detail');
                        //$this->modelCombo->deleteAll(array('menuParentId'=>$idFormPost));
                        $this->modelMenuItem->deleteAll(array('menuId' => $id));
                        foreach ($combos as $combo) {
                            $this->addNewCombo($combo, $newMenuInserted->getId());
                        }

                        $menuItem = $this->params()->fromPost('item');
                        if ($menuItem != '') {
                            foreach ($menuItem as $item) {
                                $this->addNewMenuItem($item, $newMenuInserted->getId());
                            }
                        }
                        //end edit combo in menu
                    }
                }
                $this->flashMessenger()->addSuccessMessage("Update success");
                $this->redirect()->toRoute('admin/child',array('controller'=>'index'));
            }
            $combo = $this->modelCombo->findBy(array('isdelete' => '0', 'menuParentId' => $menu->getId()));
            $menuItems = $this->modelMenuItem->findBy(array('menuId' => $menu->getId()));
            return new ViewModel(array(
                'data' => $menu,
                'title' => $this->translator->translate('Edit Menu: ') . $menu->getName(),
                'combos' => $combo,
                'menuItems' => $menuItems

            ));
        }
    }

    private function addNewCombo($data, $parentId)
    {
        $combo = new MenuCombo();
        $combo->setMenuParentId($parentId);
        $combo->setMenuChildId($data['menuid']);
        $combo->setMenuQuantity($data['menuQuantity']);
        $combo->setIsdelete(0);
        $this->modelCombo->insert($combo);
    }

    private function addNewMenuItem($data, $parentId)
    {
        $menuItem = new MenuItem();
        $menuItem->setMenuId($parentId);
        $menuItem->setMenuStoreId($data['menu_store_id']);
        $menuItem->setQuantity($data['quantity']);
        $menuItem->setUnit($data['unit']);
        $this->modelMenuItem->insert($menuItem);
    }

    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->params()->fromPost('id');
            $menu = $this->modelMenu->findOneBy(array('id' => $id));
            $menu->setIsdelete(1);
            $this->modelMenu->edit($menu);
            //$this->model->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }

    public function detailAction()
    {
        $id = $this->params()->fromRoute('id');
        $menuInfo = $this->modelMenu->findOneBy(array('id' => $id));
        $dataRow = $this->modelMenu->convertSingleToArray($menuInfo);

        $dataDetail = array(
            'title' => $this->translator->translate('Detail') . ': ' . $menuInfo->getName(),
            'link' => 'admin/index',
            'data' => $dataRow,
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

        $menusCombo = $this->modelCombo->findBy(array('isdelete' => '0', 'menuParentId' => $id));

        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelCombo->convertToArray($menusCombo);
        $dataChild = array(
            'tableTitle' => $this->translator->translate('Manage child combo'),
            'link' => 'admin/combo',
            'data' => $dataRow,
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
            'hideDeleteButton' => 0,
            'hideEditButton' => 0,
        );

        return new ViewModel(array('data' => $dataDetail, 'dataChild' => $dataChild));
    }


    public function addAjaxAction(){

        if($this->getRequest()->isPost()){
            $menu = new Menu();
            $menu->setName($this->params()->fromPost('name'));
            $menu->setCost($this->params()->fromPost('cost'));
            $menu->setTakeAwayCost($this->params()->fromPost('tacost'));
            $menu->setCatId($this->params()->fromPost('cat_id'));
            $menu->setIsCombo(0);
            $menu->setIsdelete(1);
            $menu->setDescription('');
            $menu->setImage(' ');
            $productInserted = $this->modelMenu->insert($menu);
            $idInserted = $productInserted->getId();
            $menu = $this->modelMenu->findOneBy(array('id'=>$idInserted));
            echo json_encode($this->modelMenu->convertSingleToArray($menu));
            die;
        }
        echo 0;
        die;
    }
}