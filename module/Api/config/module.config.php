<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/13/2014
 * Time: 3:16 PM
 */
namespace Api;
return array(
    'controllers' => array(
        'invokables' => array(
            'Api\Controller\Index' => 'Api\Controller\IndexController',
            'Api\Controller\Order' => 'Api\Controller\OrderController',
            'Api\Controller\User' => 'Api\Controller\UserController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'api' => array(
                'type'    => 'Literal',
                'priority'=> 3,
                'options' => array(
                    'route'    => '/api',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Api\Controller',
                        'controller'    => 'index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'child' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:controller[/:action]][/:id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Api\Controller\index',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'paginatorpage' => array(
                        'priority'=> 2,
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[:controller][/:action][/page/:page][/sortby/:by][/sortorder/:order]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page'     => '[0-9]+',
                                'area'     => '[0-9]+',
                                'sortby'     => '[a-zA-Z]',
                                'sortorder'     => '[a-zA-Z]',
                            ),
                            'defaults' => array(
                                'controller'    => 'Index',
                                'action'        => 'index',
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
            'Api' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
        'Api' => 'layout/api',
        'print' => 'layout/print',
        'login' => 'layout/login',
        'error/404'               => __DIR__ . '/../view/error/404.phtml',
        'error/index'             => __DIR__ . '/../view/error/index.phtml',
    ),

);