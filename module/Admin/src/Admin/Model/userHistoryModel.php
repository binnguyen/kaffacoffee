<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;


use Admin\Entity\Surtax;
use Admin\Entity\UserHistory;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;

class userHistoryModel extends globalModel {

    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\UserHistory';
        parent::__construct($controller);
    }




    public function hydrator($data = array()){
        $user = new UserHistory();
        $user = $this->hydrator($data,$user);
        return $user->getAction();
    }

    public  function convertToArray($datas){
        $return = array();

        foreach($datas as $data){
            $userInfo = Utility::getUserInfo($data->getUserId());
            $array = array();
            $array['id'] = $data->getId();
            $array['userId'] =$userInfo->getFullName();
            $array['action'] = $data->getAction();
            $array['time'] =  date("d-m-y H:i:s",$data->getTime());
            $return[] = $array;
        }
        return $return;
    }



}