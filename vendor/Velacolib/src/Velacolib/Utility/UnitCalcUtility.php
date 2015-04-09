<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 10/23/14
 * Time: 11:15 AM
 */

namespace Velacolib\Utility;

use Zend\Mvc\Controller\AbstractActionController;

class UnitCalcUtility extends AbstractActionController {

    public static $option;
    public static $servicelocator;

    public static function getSM()
    {
        return self::$servicelocator;
    }

    public static function setSM($val)
    {
        self::$servicelocator = $val;
    }

    static  function unitCalc(){
        $unitCalc = array(
            'L'=>array(
                'ML'=>1000,
                'M3'=>0.001
            ),
            'ML'=> array(
              'L'=>0.001,
              'M3'=>0.000001
            ),
            'KG'=>array(
                'G'=>1000,
                'MG'=>1000000,
                'TON'=>0.001
            ),
            'G'=>array(
                'KG'=>0.001,
                'MG'=>1000,
                'TON'=> 0.000001
            ),
            'MG'=>array(
                'KG'=>0.000001,
                'G'=>0.001,
                'TON'=> 0.000001
            ),
        );

        return $unitCalc;
    }



} 