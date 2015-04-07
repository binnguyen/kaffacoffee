<?php
namespace Velacolib;

use Velacolib\Utility\TransactionUtility;
use Velacolib\Utility\Utility;
use Velacolib\Utility\Imagemoo;
use Velacolib\Utility\UnitCalcUtility;
use Velacolib\Utility\DropboxUtility;
use Velacolib\Utility\setupUtility;

use Zend\EventManager\Event;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(

                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap(Event $e){
        $app = $e->getApplication();
        Utility::setSM($app->getServiceManager());
        TransactionUtility::setSM($app->getServiceManager());
        UnitCalcUtility::setSM($app->getServiceManager());
        DropboxUtility::setSM($app->getServiceManager());
        setupUtility::setSM($app->getServiceManager());


    }
}
