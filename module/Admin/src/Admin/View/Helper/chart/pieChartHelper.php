<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 9/26/2014
 * Time: 10:03 AM
 */

namespace Admin\View\Helper\chart;

use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;


//example helper
//config in module getViewHelperConfig
class pieChartHelper extends AbstractHelper {
    public function __invoke($data = array(),$title='')
    {

        echo $this->getView()
            ->render('layout/helper/backend/chart/pieChartHelper.phtml',
            array('data'=>$data,'title'=>$title));
    }
}
//end example helper