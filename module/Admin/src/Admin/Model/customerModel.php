<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use stdClass;
use Admin\Entity\Customer;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\AuthenticationService;


class customerModel extends globalModel {

    protected $loginUser;
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\Customer';
        parent::__construct($controller);
    }

    public function hydrator($data = array()){
        $user = new Customer();
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
    public function convertSingleToArray($user){
        $array = array();
        $array['id'] = $user->getId();
        $array['fullname'] = $user->getFullname();
        $array['nicename'] = $user->getNiceName();
        $array['customerCode'] = $user->getCustomerCode();
        $array['level'] =  $user->getLevel();
        $array['phone'] =  $user->getPhone();
        $array['email'] =  $user->getEmail();
        $array['address'] =  $user->getAddress();
        $array['birthday'] =  $user->getBirthday();
        $array['avatar'] =  $user->getAvatar();
        $array['image'] = Utility::getImage('normal',$user->getAvatar());
        $array['isdelete'] =  $user->getIsdelete();
        return $array;
    }


}