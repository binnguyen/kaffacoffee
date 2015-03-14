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
use Zend\Code\Scanner\Util;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
class UserController extends ApiController
{
    protected   $modelMenu;
    protected   $modelCombo;
    protected $catModel;
    public function init(){

    }
    public function indexAction()
    {
    }

    /**
     *
     */
    public function loginAction(){
        $request = $this->getRequest();
        $result = array();
        if($request->isPost()){
            $userApi = Utility::userApi(
                $this->params()->fromQuery('userName'),
                $this->params()->fromQuery('apiKey')
            );

            $this->userId = $userApi->getId();

            $customerLogin = ($this->params()->fromPost());
            $result =   Utility::checkCustomer($customerLogin);
            if($result){
               echo  $result->getId();
            } else{
                echo 0;
            }
             die;
        }
        die('Die Hacked');
    }

    public function detailAction(){

    }
    public function pageAction(){

    }
    public function getCategoryAction(){

    }
}