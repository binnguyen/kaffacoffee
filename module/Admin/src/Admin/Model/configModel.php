<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use Admin\Entity\Config;
use stdClass;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;


class configModel extends globalModel {

    protected $loginUser;
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Config';
        parent::__construct($controller);
    }





    public function hydrator($data = array()){
        $user = new Config();
        $user = $this->hydrator($data,$user);
        return $user->getFullname();
    }


    public function convertToArray($data){
        $array = array();
        foreach($data as $item){
            $array[] = $this->convertSingleToArray($item);
        }
        return $array;
    }
    public function convertSingleToArray($config){
        $array = array();
        $array['name'] = $config->getName();
        $array['id'] = $config->getId();
        $array['value'] = $config->getValue();
        $array['type'] = $config->getType();
        return $array;
    }


    public function testSQl($sqlStr){

    }
}