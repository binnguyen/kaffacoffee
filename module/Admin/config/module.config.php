<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/13/2014
 * Time: 3:16 PM
 */
namespace Admin;
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Category' => 'Admin\Controller\CategoryController',
            'Admin\Controller\Table' => 'Admin\Controller\TableController',
            'Admin\Controller\Order' => 'Admin\Controller\OrderController',
            'Admin\Controller\Users' => 'Admin\Controller\UsersController',
            'Admin\Controller\Login' => 'Admin\Controller\LoginController',
            'Admin\Controller\Coupon' => 'Admin\Controller\CouponController',
            'Admin\Controller\Combo' => 'Admin\Controller\ComboController',
            'Admin\Controller\Report' => 'Admin\Controller\ReportController',
            'Admin\Controller\dashboard' => 'Admin\Controller\DashboardController',
            'Admin\Controller\Surtax' => 'Admin\Controller\SurtaxController',
            'Admin\Controller\Config' => 'Admin\Controller\ConfigController',
            'Admin\Controller\Menustore' => 'Admin\Controller\MenustoreController',
            'Admin\Controller\Property' => 'Admin\Controller\PropertyController',
            'Admin\Controller\History' => 'Admin\Controller\HistoryController',
            'Admin\Controller\Supplier' => 'Admin\Controller\SupController',
            'Admin\Controller\SupplierItem' => 'Admin\Controller\SupplieritemController',
            'Admin\Controller\MenustoreMain' => 'Admin\Controller\MenustoreMainController',
            'Admin\Controller\Customer' => 'Admin\Controller\CustomerController',
            'Admin\Controller\Payment' => 'Admin\Controller\PaymentController',
            'Admin\Controller\PaymentCategory' => 'Admin\Controller\PaymentCategoryController',
            'Admin\Controller\TrackingTore' => 'Admin\Controller\TrackingToreController',
            'Admin\Controller\Transaction' => 'Admin\Controller\TransactionController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'admin' => array(
                'type'    => 'Literal',
                'priority'=> 3,
                'options' => array(
                    'route'    => '/admin',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller'    => 'dashboard',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'child' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:controller[/:action]][/:id][/:filter_action][/:fromdate][/:todate]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                                'filter_action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/frontend.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'admin' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'module_layouts' => array(
        'Admin' => 'layout/backend',
        'login' => 'layout/login',
        'error/404'               => __DIR__ . '/../view/error/404.phtml',
        'error/index'             => __DIR__ . '/../view/error/index.phtml',
    ),
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        )
    )
);