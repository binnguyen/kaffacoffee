<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 12:12 AM
 */

namespace Admin\Model;

use stdClass;
use Admin\Entity\User;
use Velacolib\Utility\Utility;
use Zend\InputFilter\InputFilterInterface;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\AuthenticationService;


class userModel extends globalModel implements  AdapterInterface{

    protected $loginUser;
    function __construct($controller)
    {
        $this->entityName = 'Admin\Entity\User';
        parent::__construct($controller);
    }

    public function setLoginUser($user){
        $this->loginUser = $user;
    }
    public function getLoginUser(){
        return $this->loginUser;
    }

    public function getInputFilter($data)
    {

        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'id',
            'required' => false,
        )));

        $validator = new \DoctrineModule\Validator\NoObjectExists(array(
            'object_repository' => $this->objectManager->getRepository($this->entityName),
            'fields' => array('fullname')
        ));
        //use in check email exist when sign up
        $filter = $validator->isValid(array('fullname' => $data['fullName'])); // dumps 'true' if an entity matches
        return $filter;
    }

    public function hydrator($data = array()){
        $user = new User();
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
        $array['userName'] = $user->getUserName();
        $array['id'] = $user->getId();
        $array['password'] = $user->getPassword();
        $array['fullName'] = $user->getFullName();
        $array['type'] = Utility::getUserRole($user->getType());
        $array['api_key'] = $user->getApiKey();
        return $array;
    }

    public function authenticate(){
        $result = $this->login($this->loginUser['userName'],$this->loginUser['password']);
        if( $result ){
            $identity = new stdClass();
            $identity->userId = $result->getId();
            $identity->userName = $result->getUserName();
            $identity->userFullName = $result->getFullName();
            $identity->userPassword = $result->getPassword();
            $identity->userType = $result->getType();
            return new AuthenticationResult(
                AuthenticationResult::SUCCESS,
                $identity,
                array()
            );
        }else{
            return new AuthenticationResult(
                AuthenticationResult::FAILURE,
                null,
                array('error'=>'Login Fail'));

        }

    }

    public function login($username, $password){
        $user = $this->findOneBy(array('userName'=> $username));
        if($user){
            if(sha1($password) == $user->getPassword()){
                return $user;
            }
        }
        return null;
    }

    public function testSQl($sqlStr){

    }
}