<?php

namespace Admin\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class BaseController extends AbstractActionController
{

    const MESSANGER_TYPE_ERROR      = 'error';
    const MESSANGER_TYPE_INFO       = 'info';
    const MESSANGER_TYPE_SUCCESS    = 'success';
    const MESSANGER_TYPE_WARN       = 'warn';




    protected function addFlashMessageError($message)
    {
        $this->flashMessenger()->setNamespace(self::MESSANGER_TYPE_ERROR)->addMessage($message);
    }

    protected function addFlashMessageInfo($message)
    {
        $this->flashMessenger()->setNamespace(self::MESSANGER_TYPE_INFO)->addMessage($message);
    }

    protected function addFlashMessageSuccess($message)
    {
        $this->flashMessenger()->setNamespace(self::MESSANGER_TYPE_SUCCESS)->addMessage($message);
    }

    protected function addFlashMessageWarn($message)
    {
        $this->flashMessenger()->setNamespace(self::MESSANGER_TYPE_WARN)->addMessage($message);
    }


    /**
     *
     * @return Doctrine\ORM\EntityManager
     */


    public function getEntityManager()
    {
        return $this->getServiceLocator()
            ->get('doctrine.entitymanager.orm_default');
    }

    /**
     * Add a message to the flashMessanger and redirect to the index action of the
     * current route
     */
    protected function objectNotFound()
    {
        $this->addFlashMessageWarn("messages.object.donotexists");
        return $this->redirect()->toRoute(null, array(
            'action' => 'index'
        ));
    }

    /**
     * Get the query string offset parameter for data tables
     * @param $default int
     * @return int
     */
    protected function getDataTableQueryOffset($default = 0)
    {
        return (int) $this->getRequest()->getQuery('iDisplayStart', $default);
    }

    /**
     * Get the query string limit parameter for data tables
     * @param $default int
     * @return int
     */
    protected function getDataTableQueryLimit($default = 10)
    {
        return (int) $this->getRequest()->getQuery('iDisplayLength', $default);
    }

    /**
     * Get the query string sorting column parameter for data tables
     * @param $default int
     * @return int
     */
    protected function getDataTableQuerySortingColumn($default = 1)
    {
        $sortCol = (int) $this->getRequest()->getQuery('iSortCol_0', $default);
//        if($sortCol == 0) {
//            $sortCol = 1;
//        }
        return $sortCol;
    }

    /**
     * Get the query string sorting direction parameter for data tables
     * @param $default string
     * @return string
     */
    protected function getDataTableQuerySortingDirection($default = 'asc')
    {
        return (string) $this->getRequest()->getQuery('sSortDir_0', 'asc');
    }

    /**
     * Get the query string searching parameter for data tables
     * @return string
     */
    protected function getDataTableQuerySearch()
    {
        return (string) $this->getRequest()->getQuery('sSearch', '');
    }

    /**
     * Construct the where condition fields based on search parameter
     * @param $fields array
     * @param $search string
     * @return array
     */
    protected function getDataTableWhereFields($fields, $search)
    {
        $whereFields = array();
        if ( !empty($search) ) {
            for ( $i = 0; $i < count($fields); $i++ ) {
                $searchableField = 'bSearchable_' . $i;
                //\Zend\Debug\Debug::dump($searchableField);
                $isSearchableField = $this->getRequest()->getQuery($searchableField, false);
                //\Zend\Debug\Debug::dump($isSearchableField);
                if ( $isSearchableField ) {
                    $whereFields[] = $fields[$i];
                }
            }
        }
        return $whereFields;
    }

    /**
     * @param $customSql
     * @return string
     */

    protected function customWhereSql($customSql = ''){

        if($customSql != ''){
            return ' WHERE '.$customSql;
        }
        return '';

    }



    /**
     * Construct dql where condition for data tables
     * @param $entityAlias string
     * @param $fields array
     * @param $search string
     * @param $customWhere string
     * @return string string
     */



    protected function getDataTableWhereDql($entityAlias, $fields, $search,$customWhere)
    {

        $whereFields = $this->getDataTableWhereFields($fields, $search,$customWhere);

        $dqlWhere = '';


        if ( !empty($whereFields) ) {
            $i = 0;

            $customSql  = $this->customWhereSql($customWhere) ;

//            if($customSql != ''){
//                $dqlWhere .= ' AND ';
//            }else{
                $dqlWhere .= ' WHERE ';
//            }



            foreach ( $whereFields as $field ) {
                if ( $i > 0 ) {
                    $dqlWhere .= ' OR ';
                }
                $dqlWhere .= ' ' . $entityAlias . '.' . $field . " LIKE :search ";
                $i++;
            }

            if($customWhere !='') $dqlWhere .= ' AND '.$customWhere;
            return $dqlWhere;
        }


        return $dqlWhere;
    }

    /**
     * Construct dql order condition for data tables
     * @param $entityAlias string
     * @param $fields array
     * @param $sortCol int
     * @param $sortDirection string
     * @return string
     */
    protected function getDataTableOrderDql($entityAlias, $fields, $sortCol, $sortDirection)
    {
        return ' ORDER BY c.' . $fields[$sortCol] . ' ' . strtoupper($sortDirection);
    }

    /**
     * Construct and return a standardized version of the results for data tables
     * @param $ret array
     * @param $count int
     * @param $dqlWhere string
     * @return \Zend\View\Model\JsonModel
     */
    protected function getDataTableJsonResponse($ret, $count, $dqlWhere)
    {
        return new JsonModel(
            array
            (
                'sEcho' => (int) $this->getRequest()->getQuery('sEcho'),
                'iTotalRecords' => $count,
                'iTotalDisplayRecords' => empty($dqlWhere) ? $count : count($ret),
                'results' => $ret

            )
        );
    }
}