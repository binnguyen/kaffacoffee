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
        $user = new Menu();
        $user = $this->hydrator($data,$user);
        return $user->getFullname();
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

    public function reportCategories($startDate = '',$endDate = ''){
        $strQuery = '';

        if($startDate !='')
            $strQuery .= ' AND o.time >= \''.$startDate.'\'';

        if($endDate != ''){
            $strQuery .= ' AND o.time <= \''.$endDate.'\'';

        }
        $querybuilder = $this->objectManager->getRepository($this->entityName)
            ->createQueryBuilder('c');
        $rs = $querybuilder
            ->select('  c.name category_name,
                        c.id AS category_id,
                        o.time AS order_create_time,
                        SUM(o.quantity) AS Quantity,
                        SUM(o.realCost) AS cost
                        ')
            ->from(' Admin\Entity\OrderDetail','o')
            ->from(' Admin\Entity\Menu','m')
            ->where(' o.menuId = m.id AND c.id = m.catId '.$strQuery)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();

        return $rs;
    }


}