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
class ajaxApiHelper extends tableHelper {
    public function __invoke($data,$detail = 0)
    {
        if($detail == 0)
            echo $this->getView()->render('layout/helper/ajaxApiHelper.phtml',$data);
        else
            echo $this->getView()->render('layout/helper/ajaxDetailApiHelper.phtml',$data);

    }
}
//end example helper