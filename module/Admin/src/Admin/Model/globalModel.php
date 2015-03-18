<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/13/2014
 * Time: 6:33 PM
 */
namespace Admin\Model;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Doctrine\ORM\Query;


abstract class   globalModel {
    protected $entityName;
    protected $objectManager;
    protected $hydratorService ;
    protected $querybuilder;

    function __construct($controller)
    {
        $this->objectManager = $controller;
        $this->hydratorService  = new DoctrineObject($this->objectManager,$this->entityName);
        $this->querybuilder = $this->objectManager->getRepository($this->entityName);
    }
    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param mixed $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return mixed
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param mixed $objectManager
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return DoctrineObject
     */
    public function getHydratorService()
    {
        return $this->hydratorService;
    }

    /**
     * @param DoctrineObject $hydratorService
     */
    public function setHydratorService($hydratorService)
    {
        $this->hydratorService = $hydratorService;
    }

    /**
     * @return mixed
     */
    public function getQuerybuilder()
    {
        return $this->querybuilder;
    }

    /**
     * @param mixed $querybuilder
     */
    public function setQuerybuilder($querybuilder)
    {
        $this->querybuilder = $querybuilder;
    }


    /*
     * start function
     *
     */

    /**
     * begin transaction
     */
    public function begin(){
        $this->objectManager->getConnection()->beginTransaction();
    }

    /**
     * transaction commnit
     */
    public  function commit()
    {
        $this->objectManager->getConnection()->commit();
    }

    /**
     * Rolls back a transaction.
     */
    public function rollback()
    {
        $this->objectManager->getConnection()->rollback();
    }


    public function findOneBy($array){

        //fetch one by
        $resault = $this->objectManager->getRepository($this->entityName)->findOneBy($array);
        return $resault;

    }

    public function findAll(){

        $resault = $this->objectManager->
        getRepository($this->entityName)
            ->findAll();
        return $resault;
    }

    public function findBy($array, $oderArray = array(), $limit = null, $offet = null){
        $resault = $this->objectManager->getRepository($this->entityName)
            ->findBy($array,$oderArray,$limit,$offet);
        return $resault;
    }

    public function insert($object){
        $this->objectManager->persist($object);
        //add, edit, delete must $this->objectManager->flush();
        $this->objectManager->flush();
        //get last id
        return $object; // yes, I'm lazy
    }

    public function deleteAll($array){
        $objects = $this->objectManager->getRepository($this->entityName)->findBy($array);
        if($objects){
            foreach($objects as $item){
                $this->objectManager->remove($item);
                $this->objectManager->flush();
            }
        }
    }

    public function delete($array){
        $object = $this->objectManager->getRepository($this->entityName)->findOneBy($array);
        //delete user
        if($object){
            $this->objectManager->remove($object);
            //add, edit, delete must $this->objectManager->flush();
            $this->objectManager->flush();
        }
    }

    public function edit($object){
        $this->objectManager->merge($object);
        //add, edit, delete must $this->objectManager->flush();
        $this->objectManager->flush();
    }

    public function paginator($whereArray =  array(),$currentPage , $perPage){
        $str = 'SELECT  obj FROM '.$this->entityName.' obj ';
        $where_str = '';
        foreach($whereArray as  $where){

            if($where_str == '')
                $where_str .= 'WHERE '.$where;
            else
                $where_str .= ''.$where;
        }
        $str .=  $where_str;
        $query = $this->objectManager->createQuery($str);
        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator(new ORMPaginator($query))
        );
        $paginator
            ->setCurrentPageNumber($currentPage)
            ->setItemCountPerPage($perPage);
        return $paginator;
    }

    /**
     * @param array $data
     * @return mixed
     */
    abstract public  function hydrator($data = array());

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
    public  function convertSingleToArray($datas){
        $array = array();
        $array['id'] = $datas->getId();
        $array['name'] = $datas->getName();
        return $array;
    }


    //use when create builder sql str
    public function createQuery($strQuery,$limit = null, $offset = null){
        $query = $this->querybuilder->createQueryBuilder('c');
        $return = $query->select('c')
            ->where($strQuery)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
        return $return;
    }

    public function createQueryTest($strQuery,$limit = null, $offset = null){
        $query = $this->querybuilder->createQueryBuilder('c');
        $return = $query->select('c')
//            ->where('c.isdelete = :isdelete  AND c.name = :name ')
            ->where('c.isdelete = :isdelete ')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('c.id','DESC')
            ->setParameter(':isdelete','0')
            ->getQuery()
            ->getResult();
        return $return;
    }
}
