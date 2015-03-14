<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Velacolib\Utility\Utility;
use Admin\Entity\Managetable;
use Admin\Entity\Table;
use Admin\Model\tableModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class TableController extends BaseController
{
    protected   $modelTable;
    protected   $translator;

    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $manageTable = $this->sm->get($service_locator_str);
        $this->translator = Utility::translate();

        //check login
        $user = Utility::checkLogin($this);
        if(! is_object($user) && $user == 0){
            $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }else{
            $isPermission = Utility::checkRole($user->userType,ROLE_ADMIN);
            if( $isPermission == false)
                $this->redirect()->toRoute('admin/child',array('controller'=>'login'));
        }
        //end check login

        $this->modelTable = new tableModel($manageTable);
        return parent::onDispatch($e);
    }

    public function ajaxListAction(){

        $fields = array(
            'id',
            'name',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn();
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = ' c.isdelete = 0 ';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }
        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\Managetable c";

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql. $customQuery . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\Managetable c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();
        // map data
        $ret = array_map(function($item) {
            // create link
            $linkEdit =   '/admin/table/add/'.$item->getId() ;
            $linkDelete =  '/admin/table/delete/'.$item->getId() ;
            $linkDetail =   '/admin/table/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'name' => $item->getName(),
                'action'=>
                '
                 <a  target="_blank" href="'.$linkEdit.'" class="btn btn-primary"><i class="icon-edit-sign"></i></a>
                 <a  id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="javascript:void(0)" class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }


    public function indexAction()
    {
        $table = $this->modelTable->findBy(array('isdelete'=>'0'));


        //tableTitle = table heading
        //datarow row of table... render by heading key
        //heading key = table column name
        $dataRow = $this->modelTable->convertToArray($table);
        $data =  array(
            'tableTitle'=> $this->translator->translate('Manager table'),
            'link' => 'admin/table',
            'data' =>$dataRow,
            'heading' => array(
                'id' => 'Id',
                'name' => $this->translator->translate('Name')
            ),
            'hideDetailButton' => 1
        );
        return new ViewModel(array('data'=>$data,'title'=> $this->translator->translate('Table')));
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
            return new ViewModel(array('title'=>$this->translator->translate('Add new table')));
        }
        else{

            $table = $this->modelTable->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $idFormPost = $this->params()->fromPost('id');
                $table = $this->modelTable->findOneBy(array('id'=>$idFormPost));
                $table->setName($this->params()->fromPost('name'));
                $this->modelTable->edit($table);

                //flash
                $this->flashMessenger()->addSuccessMessage("Update success");
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