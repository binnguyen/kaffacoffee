<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 9/26/2014
 * Time: 10:03 AM
 */

namespace Admin\View\Helper\frontend\common;

use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;


//example helper
//config in module getViewHelperConfig
class leftMenuFeHelper extends AbstractHelper {
    public function __invoke($text = '')
    {
        echo $this->getView()->render('layout/helper/frontend/common/leftMenuFeHelper.phtml');
    }
}
//end example helper