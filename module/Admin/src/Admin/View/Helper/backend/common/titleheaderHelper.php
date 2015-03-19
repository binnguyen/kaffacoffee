<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 9/26/2014
 * Time: 10:03 AM
 */

namespace Admin\View\Helper\backend\common;

use Zend\View\Helper\AbstractHelper;


//example helper
//config in module getViewHelperConfig
class titleheaderHelper extends AbstractHelper {

    public function __invoke($text = '')
    {
        echo $this->getView()->render('layout/helper/backend/common/titleheaderHelper.phtml',array('title'=>$text));
    }
}
//end example helper