<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/13/2014
 * Time: 3:14 PM
 */
namespace Admin;
define('ROLE_ADMIN', 'admin');
define('ROLE_USER','user');
define('ADD_ORDER',-1);
define('INSERT_STORE',1);
define('ADD_ORDER_ACTION', 'X');
define('INSERT_STORE_ACRION', 'N');
define('MAIN_STORE', 'main');
define('SUB_STORE', 'sub');
define('API_STRING','velaapp_api');

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    //set layout for admin module
    public function onBootstrap($e)
    {
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function($e) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config          = $e->getApplication()->getServiceManager()->get('config');
            if (isset($config['module_layouts'][$moduleNamespace])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]);
            }
        }, 100);

        $em = $e->getApplication()->getEventManager();
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'doctrine' => function($sm){
                    $dbAdapter = $sm->get('Doctrine\ORM\EntityManager');
                    // return your shiny new service
                    return $dbAdapter;
                }
            )
        );

    }


    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'newHelper'   => 'Admin\View\Helper\backend\common\newHelper',
                'styleHelper'   => 'Admin\View\Helper\backend\common\styleHelper',
                'scriptHelper'   => 'Admin\View\Helper\backend\common\scriptHelper',
                'leftMenuHelper'   => 'Admin\View\Helper\backend\common\leftMenuHelper',
                'headerHelper'   => 'Admin\View\Helper\backend\common\headerHelper',
                'titleHelper'   => 'Admin\View\Helper\backend\common\titleheaderHelper',
                'tableHelper'   => 'Admin\View\Helper\tableHelper',
                'ajaxApiHelper'   => 'Admin\View\Helper\ajaxApiHelper',
                'tableOrderHelper'   => 'Admin\View\Helper\tableOrderHelper',
                'detailHelper'   => 'Admin\View\Helper\detailHelper',
                'addorderHelper'   => 'Admin\View\Helper\addorderHelper',
                'leftMenuFeHelper'   => 'Admin\View\Helper\frontend\common\leftMenuFeHelper',
                'printHelper'        => 'Admin\View\Helper\printHelper',
                'formHelper'   => 'Admin\View\Helper\formHelper',
                'reportHelper' => 'Admin\View\Helper\reportUserEndDay',
                'manageTableHelper' => 'Admin\View\Helper\manageTableHelper',
                'flashHelper' => 'Admin\View\Helper\flashHelper',
                'pieChartHelper'   => 'Admin\View\Helper\chart\pieChartHelper',
                'lineChartHelper'   => 'Admin\View\Helper\chart\lineChartHelper'
            )
        );
    }
}