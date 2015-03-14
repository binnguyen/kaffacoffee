<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/13/2014
 * Time: 3:16 PM
 */
namespace Frontend;
return array(
    'controllers' => array(
        'invokables' => array(
            'Frontend\Controller\Login' => 'Frontend\Controller\LoginController',
            'Frontend\Controller\Index' => 'Frontend\Controller\IndexController',
            'Frontend\Controller\Order' => 'Frontend\Controller\OrderController',
            'Frontend\Controller\Category' => 'Frontend\Controller\CategoryController',
            'Frontend\Controller\Com' => 'Frontend\Controller\ComController',
            'Frontend\Controller\Backup' => 'Frontend\Controller\BackupController',
            'Frontend\Controller\Table' => 'Frontend\Controller\TableController',
            'Frontend\Controller\Customer' => 'Frontend\Controller\CustomerController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'frontend' => array(
                'type'    => 'Literal',
                'priority'=> 3,
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'controller'    => 'table',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'child' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => 'frontend[/:controller[/:action]][/:id][/topic/:topic_id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                                'topic_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Frontend\Controller\index',
                                'action'     => 'index',
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
        'template_path_stack' => array(
            'frontend' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
        'Frontend' => 'layout/frontend',
        'print' => 'layout/print',
        'login' => 'layout/login',
        'error/404'               => __DIR__ . '/../view/error/404.phtml',
        'error/index'             => __DIR__ . '/../view/error/index.phtml',
    ),

);