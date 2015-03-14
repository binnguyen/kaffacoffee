<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\TrackingTore;
use Zend\InputFilter\InputFilterInterface;

class trackingToreModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\TrackingTore';
        parent::__construct($controller);
    }




    public function hydrator($data = array()){
        $user = new TrackingTore();
        $user = $this->hydrator($data,$user);
        return $user->getName();
    }

    public  function convertToArray($datas){
        $return = array();
        foreach($datas as $data){
            $array = array();
            $array['id'] = $data->getId();
            $array['name'] = $data->getName();
            $array['quantity'] = $data->getQuantity();
            $array['supplierItemId'] = $data->getSupplierItemId();
            $array['supplierItemName'] = $data->getSupplierItemName();
            $array['note'] = $data->getNote();
            $array['time'] = $data->getTime();
            $return[] = $array;
        }
        return $return;
    }


    public function reportTracking($startDate= '', $endDate = ''){
        $strQuery = '';

        if($startDate !='')
            $strQuery .= ' AND t.time >= \''.$startDate.'\'';

        if($endDate != ''){
            $strQuery .= ' AND t.time <= \''.$endDate.'\'';

        }
        $querybuilder = $this->objectManager->getRepository($this->entityName)
            ->createQueryBuilder('t');
        $rs = $querybuilder
            ->select('  t.name tracking_name,
                        t.quantity AS tracking_quantity,
                        t.id AS tracking_id,
                        t.supplierItemId AS supplier_item_id,
                        t.supplierItemName AS supplier_item_name,
                        t.note AS note,
                        t.time
                        ')
            ->where(' 1 = 1 '.$strQuery)
            ->getQuery()
            ->getResult();

        return $rs;
    }

}