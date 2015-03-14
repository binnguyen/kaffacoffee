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
use Velacolib\Utility\Utility;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

class ApiController extends AbstractActionController
{
    protected  $doctrineService;
    protected  $translator;
    protected  $postPerPage;
    protected $userId;
    public function onDispatch(\Zend\Mvc\MvcEvent $e){
        $service_locator_str = 'doctrine';
        $this->postPerPage = 10;
        $this->sm = $this->getServiceLocator();
        $this->doctrineService = $this->sm->get($service_locator_str);
        $this->translator = Utility::translate();
        $this->init();
        return parent::onDispatch($e);
    }
    public function init(){

    }
}