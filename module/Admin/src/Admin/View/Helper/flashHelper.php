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
class flashHelper extends AbstractHelper {
    public function __invoke($text = 'New Massage')
    {
        $data = array('data'=>$text);
        echo $this->getView()->render('layout/helper/flashHelper.phtml',$data);
    }
}
//end example helper