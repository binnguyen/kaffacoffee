<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\Categories;
use Zend\InputFilter\InputFilterInterface;

class categoryModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Categories';
        parent::__construct($controller);
    }




    public function hydrator($data = array()){

        $cat = new Categories();
        $data['isdelete'] = 0;
        $cat = $this->hydrator($data,$cat);
        echo '<pre>';
        print_r($cat);
        echo '</pre>';
        die;
        return $user->getName();
    }

    public  function convertToArray($datas){
        $return = array();
        foreach($datas as $data){
            $array = array();
            $array['id'] = $data->getId();
            $array['name'] = $data->getName();
            $return[] = $array;
        }
        return $return;
    }


}