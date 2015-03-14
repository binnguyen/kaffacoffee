<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 9/26/2014
 * Time: 10:03 AM
 */

namespace Admin\View\Helper;

use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;


//example helper
//config in module getViewHelperConfig
class reportUserEndDay extends AbstractHelper {
    public function __invoke($data)
    {
        echo $this->getView()->render('layout/helper/reportUserEndDay.phtml',array('data'=>$data));
    }
}
//end example helper