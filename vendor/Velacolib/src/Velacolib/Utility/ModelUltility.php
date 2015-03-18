<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 11/14/2014
 * Time: 9:30 PM
 */

namespace Velacolib\Utility;
use Admin\Entity\Categories;
use Admin\Entity\RolePermission;
use Admin\Entity\User;
use Admin\Model\categoryModel;
use Admin\Model\permissionModel;
use Admin\Model\userModel;

class ModelUltility extends Utility
{
    public static function getMenuAdmin()
    {
        echo '</pre>';
        $menu = new menuModel(self::$doctrineService);
        echo '<pre>';
        print_r($menu->findAll());
        echo '</pre>';
    }

    public static  function getCategoryForSelect(){
        $categoryModel = new categoryModel(self::$doctrineService);
        $cat = $categoryModel->findBy(array('isdelete'=>0));
        $return = array();
        foreach($cat as $item){
            $return[$item->getId()] = $item->getName();
        }
        return $return;
    }

    public static  function getIsCombo(){
        $return = array('1'=>'Combo','0'=>'No Combo');
        return $return;
    }

    public static  function getCategory($catId){
        $catModel = new categoryModel(self::$doctrineService);
        $catInfo =  $catModel->findOneBy(array('id'=>$catId));
        if($catInfo)
            return $catInfo;
        return new Categories();
    }

    public static function getPermission($query){
        $permissionModel = new permissionModel(self::$doctrineService);
        $permission = $permissionModel->findBy($query);
        $permission = $permissionModel->convertToArray($permission);
        return $permission;
    }

    public static function getUser($query = array()){
        $userModel = new userModel(self::$doctrineService);
        $userInfo = $userModel->findOneBy($query);
        if($userInfo)
            return $userInfo;
        return new User();
    }
}

