<?php
/**
 * Created by PhpStorm.
 * User: trisatria
 * Date: 1/6/14
 * Time: 1:17 PM
 */
namespace Admin\Controller;
use Admin\Entity\Categories;
use Admin\Entity\UserHistory;
use Admin\Entity\User;
use Admin\Model\userHistoryModel;
use Velacolib\Utility\Utility;
use Admin\Model\userModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;


class HistoryController extends BaseController
{
    protected   $modelHistory;
    protected   $translator;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){

        $service_locator_str = 'doctrine';
        $this->sm = $this->getServiceLocator();
        $CategoriesTable = $this->sm->get($service_locator_str);
        $this->modelHistory = new userHistoryModel($CategoriesTable);
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

        return parent::onDispatch($e);
    }

    public function ajaxListAction(){

        $fields = array(
            'id',
            'userId',
            'action',
            'time',
        );

        $offset = $this->getDataTableQueryOffset();
        $limit = $this->getDataTableQueryLimit();
        $sortCol = $this->getDataTableQuerySortingColumn(3);
        $sortDirection = $this->getDataTableQuerySortingDirection();
        $search = $this->getDataTableQuerySearch();
        $customWhere  = '';
        // WHERE conditions

        $customQuery = $this->customWhereSql($customWhere);


        $dqlWhere = $this->getDataTableWhereDql('c', $fields, $search,$customWhere);

        if ( !empty($dqlWhere) ) {
            $customQuery = '';
        }

        // ORDERING
        $dqlOrder = $this->getDataTableOrderDql('c', $fields, $sortCol, $sortDirection);

        // DQL
        $dql = "SELECT c FROM Admin\Entity\UserHistory c";

        $customWhere = $this->customWhereSql();

        // RESULTS
        $query = $this->getEntityManager()->createQuery($dql . $customWhere . $dqlWhere . $dqlOrder);
        if ( !empty($dqlWhere) ) {
            $query->setParameter(':search', '%' . $search . '%');
        }
        $results = $query->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // TOTAL RESULTS COUNT
        $countDql = "SELECT COUNT(c.id) FROM Admin\Entity\UserHistory c";
        $count = $this->getEntityManager()->createQuery($countDql)->getSingleScalarResult();
        // map data
        $ret = array_map(function($item) {
            $userInfo = Utility::getUserInfo($item->getUserId());
            // create link
            $linkEdit =   '/admin/history/edit/'.$item->getId() ;
            $linkDelete =  '/admin/history/delete/'.$item->getId() ;
            $linkDetail =   '/admin/history/detail/'.$item->getId() ;
            return array(
                'id' => $item->getId(),
                'userId' => $userInfo->getUserName(),
                'actions' => ($item->getAction()),
                'time' => date("d-m-Y",$item->getTime()),
                'action'=> '
                <a id="'.$item->getId().'"  data-link="'.$linkDelete.'" data-id="'.$item->getId().'" href="#" class="btn btn-danger btn-delete"><i class="icon-trash"></i></a>'
            );
        }, $results);

        return $this->getDataTableJsonResponse($ret, $count, $dqlWhere);

    }


    public function indexAction()
    {

        return new ViewModel(array('title'=>$this->translator->translate('Manager history')));
    }
    public function addAction()
    {
        $request = $this->getRequest();
        $id = $this->params()->fromRoute('id');
        //insert
        if($id == ''){
            if($request->isPost()) {
                $cat = new User();
                $cat->setUserName($this->params()->fromPost('userName'));
                $cat->setFullName($this->params()->fromPost('fullName'));
                $cat->setPassword(sha1($this->params()->fromPost('password')));
                $cat->setIsdelete(0);
                $cat->setType(0);
                $userInserted = $this->modelUsers->insert($cat);
            }
            //insert new user
            //$this->redirect()->toRoute('admin/child',array('controller'=>'category'));
            return new ViewModel(array('title'=> $this->translator->translate('Add new user')));
        }
        //update
        else{

            $cat = $this->modelUsers->findOneBy(array('id'=>$id));
            if($request->isPost()){
                $idFormPost = $this->params()->fromPost('id');
                $cat = $this->modelUsers->findOneBy(array('id'=>$idFormPost));
                $cat->setUserName($this->params()->fromPost('userName'));
                $cat->setFullName($this->params()->fromPost('fullName'));
                if($this->params()->fromPost('password') != ''){
                    $cat->setPassword(sha1($this->params()->fromPost('password')));
                }

                $this->modelUsers->edit($cat);
            }
            return new ViewModel(array(
                'data' =>$cat,
                'title' => $this->translator->translate('Edit User:').$cat->getUserName()
            ));
        }
    }
    public function deleteAction()
    {
        //get user by id
        $request = $this->getRequest();
        if($request->isPost()){
            $id = $this->params()->fromPost('id');
//            $menu = $this->modelUsers->findOneBy(array('id'=>$id));
//            $menu->setIsdelete(1);
//            $this->modelUsers->edit($menu);
            $this->modelUsers->delete(array('id'=>$id));
            echo 1;
        }
        die;

    }

    public function editAction()
    {


    }

}