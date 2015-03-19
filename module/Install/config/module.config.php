<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/13/2014
 * Time: 3:16 PM
 */
namespace Install;
return array(
    'controllers' => array(
        'invokables' => array(
            'Install\Controller\Index' => 'Install\Controller\IndexController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'install' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/install[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'install\Controller\index',
                        'action'     => 'index',
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
            'install' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
        'install' => 'layout/install/installlayout',
        'error/404'               => __DIR__ . '/../view/error/404.phtml',
        'error/index'             => __DIR__ . '/../view/error/index.phtml',
    ),

);