<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 11/5/2014
 * Time: 5:38 PM
 */
namespace Velacolib\Utility\Table;
use Velacolib\Utility\ModelUltility;
use Admin\Entity\User;
define('_DESC', 'desc');
define('_ASC', 'asc');


class Table
{
    protected   $selectedRow;
    protected   $selecttion;
    protected   $tableData;
    protected   $tableColumns;
    protected   $lang;
    protected   $actionLink;
    //column for select all
    protected   $actionColumn;
    //end column for select all
    protected   $actionEdit;
    protected   $actionDelete;
    protected   $actionDeleteAll;
    protected   $actionDetail;
    protected   $actionAdd;
    protected   $showDelete;
    protected   $showDetail;
    protected  $showAdd;
    protected   $showEdit;
    protected   $controlColumn;
    protected   $sortColumn;
    protected   $sortOrder;
    protected $dataModel;
    protected $translator;
    protected $buttonTextAdd;
    protected $buttonTextEdit;
    protected $buttonTextDelete;
    protected $buttonTextDetail;
    protected $buttonTextSelectAll;
    protected $buttonTextDeleteAll;
    protected $extendSQl;
    protected $extendJoin;
    protected $tablePrefix;
    //getter setter
    /**
     * @return int
     */
    public function getActionColumn()
    {
        return $this->actionColumn;
    }

    /**
     * @param int $actionColumn
     */
    public function setActionColumn($actionColumn)
    {
        $this->actionColumn = $actionColumn;
    }

    /**
     * @return string
     */
    public function getActionDelete()
    {
        return $this->actionDelete;
    }

    /**
     * @param string $actionDelete
     */
    public function setActionDelete($actionDelete = 'delete')
    {
        $this->actionDelete = $actionDelete;
    }

    /**
     * @return string
     */
    public function getActionDetail()
    {
        return $this->actionDetail;
    }

    /**
     * @param string $actionDetail
     */
    public function setActionDetail($actionDetail = 'detail')
    {
        $this->actionDetail = $actionDetail;
    }

    /**
     * @return string
     */
    public function getActionEdit()
    {
        return $this->actionEdit;
    }

    /**
     * @param string $actionEdit
     */
    public function setActionEdit($actionEdit = 'Edit')
    {
        $this->actionEdit = $actionEdit;
    }

    /**
     * @return string
     */
    public function getActionLink()
    {
        return $this->actionLink;
    }

    /**
     * @param string $actionLink
     */
    public function setActionLink($actionLink)
    {
        $this->actionLink = $actionLink;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    public function getSelectedRow()
    {
        return $this->selectedRow;
    }

    /**
     * @param string $selectedRow
     */
    public function setSelectedRow($selectedRow)
    {
        $this->selectedRow = $selectedRow;
    }

    /**
     * @return string
     */
    public function getSelecttion()
    {
        return $this->selecttion;
    }

    /**
     * @param string $selecttion
     */
    public function setSelecttion($selecttion)
    {
        $this->selecttion = $selecttion;
    }

    /**
     * @return int
     */
    public function getShowDelete()
    {
        return $this->showDelete;
    }

    /**
     * @param int $showDelete
     */
    public function setShowDelete($showDelete)
    {
        $this->showDelete = $showDelete;
    }

    /**
     * @return int
     */
    public function getShowDetail()
    {
        return $this->showDetail;
    }

    /**
     * @param int $showDetail
     */
    public function setShowDetail($showDetail)
    {
        $this->showDetail = $showDetail;
    }

    /**
     * @return int
     */
    public function getShowEdit()
    {
        return $this->showEdit;
    }

    /**
     * @param int $showEdit
     */
    public function setShowEdit($showEdit)
    {
        $this->showEdit = $showEdit;
    }

    /**
     * @return string
     */
    public function getTableColumns()
    {
        return $this->tableColumns;
    }

    /**
     * @param string $tableColumns
     */
    public function setTableColumns($tableColumns)
    {
        $this->tableColumns = $tableColumns;
    }

    /**
     * @return string
     */
    public function getTableData()
    {
        return $this->tableData;
    }

    /**
     * @param string $tableData
     */
    public function setTableData($tableData)
    {
        $this->tableData = $tableData;
    }

    /**
     * @return mixed
     */
    public function getControlColumn()
    {
        return $this->controlColumn;
    }

    /**
     * @param mixed $controlColumn
     */
    public function setControlColumn($controlColumn)
    {
        $this->controlColumn = $controlColumn;
    }

    /**
     * @return mixed
     */
    public function getSortColumn()
    {
        return $this->sortColumn;
    }

    /**
     * @param mixed $sortColumn
     */
    public function setSortColumn($sortColumn)
    {
        $this->sortColumn = $sortColumn;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param mixed $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return mixed
     */
    public function getActionAdd()
    {
        return $this->actionAdd;
    }

    /**
     * @param mixed $actionAdd
     */
    public function setActionAdd($actionAdd)
    {
        $this->actionAdd = $actionAdd;
    }

    /**
     * @return mixed
     */
    public function getActionDeleteAll()
    {
        return $this->actionDeleteAll;
    }

    /**
     * @param mixed $actionDeleteAll
     */
    public function setActionDeleteAll($actionDeleteAll)
    {
        $this->actionDeleteAll = $actionDeleteAll;
    }

    /**
     * @return mixed
     */
    public function getShowAdd()
    {
        return $this->showAdd;
    }

    /**
     * @param mixed $showAdd
     */
    public function setShowAdd($showAdd)
    {
        $this->showAdd = $showAdd;
    }

    /**
     * @return mixed
     */
    public function getDataModel()
    {
        return $this->dataModel;
    }

    /**
     * @param mixed $dataModel
     */
    public function setDataModel($dataModel)
    {
        $this->dataModel = $dataModel;
    }

    /**
     * @return mixed
     */
    public function getExtendSQl()
    {
        return $this->extendSQl;
    }

    /**
     * @param mixed $extendSQl
     */
    public function setExtendSQl($extendSQl)
    {
        $this->extendSQl = $extendSQl;
    }

    /**
     * @return mixed
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * @param mixed $tablePrefix
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @return array
     */
    public function getExtendJoin()
    {
        return $this->extendJoin;
    }

    /**
     * @param array $extendJoin
     */
    public function setExtendJoin($extendJoin)
    {
        $this->extendJoin = $extendJoin;
    }



    //end getter setter

    //__construct
    public function  __construct($tableColumns = array(), $tableData = array(), $actionLink = ''){
        $this->translator = ModelUltility::translate();
        $this->selectedRow = 'selected';
        $this->selecttion = '#example';
        $lang = array(
            'lengthMenu' => 'Display _MENU_ records per page',
            'zeroRecords' => 'Nothing found - sorry',
            'info' => 'Showing page _PAGE_ of _PAGES_',
            'infoEmpty' => 'No records available',
            'infoFiltered' => '(filtered from _MAX_ total records)',
        );

        $this->buttonTextAdd  = $this->translator->translate('Add');
        $this->buttonTextEdit  = $this->translator->translate('Edit');
        $this->buttonTextDelete = $this->translator->translate('Delete');
        $this->buttonTextDetail = $this->translator->translate('Detail');
        $this->buttonTextSelectAll= $this->translator->translate('Select all');
        $this->buttonTextDeleteAll= $this->translator->translate('Delete all');

        $this->lang = json_encode($lang);
        $this->tableColumns = $tableColumns;
        $this->tableData = $tableData;
        $this->actionLink = $actionLink;
        $this->actionColumn = 0;

        $this->actionEdit = '';
        $this->actionDelete = '';
        $this->actionDeleteAll = '';
        $this->actionDetail = '';
        $this->actionAdd = '';

        $this->showDetail = 0;
        $this->showEdit = 0;
        $this->showDelete = 0;
        $this->showAdd = 0;
        $this->controlColumn = count($this->tableColumns) - 1;
        $this->sortColumn = 0;
        $this->sortOrder = _DESC;
        $this->dataModel = null;
        $this->extendSQl = array();
        $this->extendJoin = array();
        $this->tablePrefix = 'table';
    }

    protected  function  createLink(){
        if($this->actionEdit != ''){
            $this->actionEdit = $this->actionLink.'/'.$this->actionEdit;
        }else{
            $this->actionEdit = $this->actionLink.'/add';
        }

        if($this->actionDetail != ''){
            $this->actionDetail = $this->actionLink.'/'.$this->actionDetail;
        }else{
            $this->actionDetail = $this->actionLink.'/detail';
        }

        if($this->actionDelete != ''){
            $this->actionDelete = $this->actionLink.'/'.$this->actionDelete;
        }else{
            $this->actionDelete = $this->actionLink.'/delete';
        }

        if($this->actionAdd != ''){
            $this->actionAdd = $this->actionLink.'/'.$this->actionAdd;
        }else{
            $this->actionAdd = $this->actionLink.'/add';
        }

        if($this->actionDeleteAll != ''){
            $this->actionDeleteAll = $this->actionLink.'/'.$this->actionDeleteAll;
        }else{
            $this->actionDeleteAll = $this->actionLink.'/deleteall';
        }

    }
    //function
    public function render(){
        $this->createLink();
        $this->script();
        $this->actionScript();
        $table  = $this->contentTableHtml();
        echo $table;
    }
    public function script(){
        ?>
        <script>
            var _table;
            $(document).ready(function() {

                //init table
                var selectedRow = '<?php  echo $this->selectedRow ?>';
                var selecttion = '<?php echo $this->selecttion ?>';
                var dataSet = <?php echo json_encode($this->tableData); ?>;
                var columns = <?php echo json_encode($this->tableColumns); ?>;
                var showDetail = <?php echo $this->showDetail; ?>;
                var showDelete = <?php echo $this->showDelete; ?>;
                var showEdit = <?php echo $this->showEdit; ?>;
                var lang = <?php echo $this->lang; ?>;

                $(selecttion +' tfoot th').each( function () {
                    var title = $(selecttion +' thead th').eq( $(this).index() ).text();
                    $(this).html( '<input class="column_filter" type="text" placeholder="Search '+title+'" />' );
                } );

                var table = $(selecttion +'').DataTable( {
                    "columns": columns,
                    "data": dataSet,
                    stateSave: true,
                    "language": lang,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    //render action
                    "aoColumnDefs": defindActionColumn(),
                    "order": [[ <?php echo $this->sortColumn ?>, '<?php echo $this->sortOrder ?>' ]]
                    //"order": [[0, 'desc' ]]
                    //end render action
                });
                //setup search column
                //Apply the search
                table.columns().eq( 0 ).each( function ( colIdx ) {
                    $( 'input', table.column( colIdx ).footer() ).on( 'keyup change', function () {
                        console.log(this.value);
                        table
                            .column( colIdx )
                            .search( this.value )
                            .draw();
                    } );
                } );
                _table = table;
                //end setup search column
                //end init table

                //function

                function defindActionColumn(){
                    if(showDelete == 0 && showDetail == 0 && showEdit == 0)
                        return '';
                    return [
                        {
                            "aTargets": [<?php echo $this->controlColumn;  ?>],
                            "mRender": function (data, type, full) {
                                var showDetailText = '';
                                if(showDetail == 1){
                                    showDetailText = '<a class="btn-xs action action-detail btn btn-blue" href="/<?php echo $this->actionDetail; ?>/' + data + '"><?php $this->buttonTextDetail ?></a>';
                                }

                                var showDeleteText = '';
                                if(showDelete == 1){
                                    showDeleteText = '<a class="btn-xs action action-detail btn btn-red " href="/<?php echo $this->actionDelete; ?>/' + data + '"><?php $this->buttonTextDelete ?></a>';
                                }

                                var showEditText = '';
                                if(showEdit== 1){
                                    showEditText = '<a class="btn-xs action action-detail btn btn-danger" href="/<?php echo $this->actionEdit; ?>/' + data + '"><?php $this->buttonTextEdit ?></a>';
                                }

                                return showDetailText+' '+showEditText+' '+showDeleteText;
                            }
                        }
                    ];
                }
                //end function

                /////end delete edit row

                ///end event

            } );
        </script>
    <?php
    }
    public function contentTableHtml(){
        $actionAddButton = ' <a class="btn btn-secondary" href="/'.$this->actionAdd.'">'.$this->buttonTextAdd .'</a>';

        if($this->showAdd == 0){
            $actionAddButton = '';
        }

        $actionBox = '<input class="btn btn-purple" type="button" id="button-select-all" value="'.$this->buttonTextSelectAll.'">
            <input class="btn btn-danger" type="button" id="button-delete" value="'.$this->buttonTextDeleteAll.'">';
        if($this->showDelete == 0){
            $actionBox = '';
        }
        $html =  '
        <div class="panel panel-default">
        <div class="panel-heading">
           '.$actionAddButton.'
            '.$actionBox.'
        </div>
        <div class="panel-body overflow-x-scroll">';
        $html .= '<table id="'.str_replace('#','',$this->selecttion).'" class="table table-striped table-bordered dataTable" cellspacing="0" width="100%">';
        $html .= '<thead>';
        foreach($this->tableColumns as $colum){
            $html .=    '<th>'.$colum['title'].'</th>';
        }
        $html .= '</thead>';
        $html .= '<tfoot>';
        foreach($this->tableColumns as $colum){
            $html .=    '<th>'.$colum['title'].'</th>';
        }
        $html .= '</tfoot>';
        $html .='</table>
            </div>
        </div>';
        return $html;
    }

    public function actionScript(){
        ?>
        <script>
            var selecttion = '<?php echo $this->selecttion ?>';
            var selectedRow = '<?php  echo $this->selectedRow ?>';
            function deleteAll(_data){
                $.ajax({
                    url : '/<?php echo $this->actionDeleteAll ?>',
                    type :'POST',
                    dataType:'json',
                    data : {data:_data }
                }).done(function(repsone){
                    //do some code

                    //end do some code
                });
            }
            ///event
            ////row clicked
            $(document).on('click', selecttion +' tbody tr', function () {
                //var name = $('td', this).eq(0).text();
                // console.log($('td',this));
                //alert( 'You clicked on '+name+'\'s row' );
            } );
            ////end row clicked

            /////select multi row
            $(document).on( 'click',selecttion +' tbody tr', function () {
                $(this).toggleClass(selectedRow);
            } );
            /////end select multi row

            /////delete selected row
            $(document).on('click','#button-select-all', function () {

                $(selecttion +' tbody tr').toggleClass(selectedRow)
            });
            //end delete selected row

            /////delete edit row
            $(document).on('click','#button-delete', function () {
                var data = '';
                var myArray = [];
                $.each( _table.rows('.selected').data(), function(){
                    ////get value in selected rows
                    //console.log($(this));
                    ///get cell index = 0
                    //console.log($(this)[0]);
                    myArray.push($(this)[<?php echo $this->actionColumn; ?>]);
                    data = JSON.stringify(myArray);

                    ///end get cell index = 0
                    ////end get value in selected rows
                    ///remove selected rows

                    _table.row('.selected').remove().draw( false );
                    ///end remove selected rows
                });
                console.log(data);
                deleteAll(data);
            });
        </script>
    <?php
    }
    //table ajax process
    public   function  getDataTableAjax($request,$columns){
        $select = $this->extendSelectProcess($columns);
        $tablePrefix = $this->tablePrefix;
        $menuQueryBuilder = $this->dataModel
            ->getQuerybuilder()
            ->createQueryBuilder($tablePrefix)
            ->select($select);
        $menuQueryBuilder = $this->extendJoinProcess($menuQueryBuilder);
        $menuQueryBuilder = $this->createFilter($menuQueryBuilder,$request,$columns,$tablePrefix);
        //order
//        $arrayOrder = array();
        $menuQueryBuilder = $menuQueryBuilder->orderBy( $this->tablePrefix.'.id','desc');
        if(isset($request['order']) && $request['order'][0] != ''){
            $columnOrderBy = $columns[$request['order'][0]['column']]['db'];
            $columnOrder = $request['order'][0]['dir'] ;
            $menuQueryBuilder = $menuQueryBuilder->orderBy($tablePrefix.'.'.$columnOrderBy,$columnOrder);
        }
        $menuQueryBuilder = $menuQueryBuilder->setMaxResults($request['length']);
        $menuQueryBuilder = $menuQueryBuilder->setFirstResult($request['start']);
        $menuData = $menuQueryBuilder
            ->getQuery()
            ->getResult();

        //end order
        if(count($select) == 0){
            $menuData =  $this->dataModel->convertToArray($menuData);
        }


        //query all then get to total
        $menuQueryBuilderTotal = $this->dataModel->getQuerybuilder()->createQueryBuilder($tablePrefix)->select($select);
        $menuQueryBuilderTotal = $this->extendJoinProcess($menuQueryBuilderTotal);
        $menuQueryBuilderTotal = $this->createFilter($menuQueryBuilderTotal,$request,$columns,$tablePrefix);
        $menuDataCount = $menuQueryBuilderTotal
            ->getQuery()
            ->getResult();
        $recordsTotal = count($menuDataCount);
        //end query all then get to total
        return array(
            "draw"            => intval( $request['draw'] ),
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered"    => intval( $recordsTotal ),
            "data"            => $this->dataOutput( $columns, $menuData )
        );
    }
    protected  function dataOutput( $columns, $data )
    {
        $out = array();

        for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
            $row = array();

            for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
                $column = $columns[$j];

                // Is there a formatter?
                if ( isset( $column['formatter'] ) ) {
                    $row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
                }
                else {
                    $row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
                }
            }

            $out[] = $row;
        }

        return $out;
    }
    protected function createFilter($menuQueryBuilder,$request, $columns,$tablePrefix){
        $str = '';
        //create query string
        $searchVal ='';
        $checkColumnSearch = $this->checkColumnSearch($menuQueryBuilder,$request,$columns,$tablePrefix);
        //search by search box
        if($request['search']['value'] != '') {
            $searchVal = $request['search']['value'];
            foreach($columns as $column ){

                $prefix = $this->tablePrefix;
                if(isset($columns['prefix']) && $column['prefix']!='')
                    $prefix = $column['prefix'];

                if($column['search']== true){
                    $operator = ' = ';
                    if($column['type'] == 'text'){
                        $operator = ' LIKE ';
                        if($str == ''){
                            $str .= ' AND  '.$prefix.'.'.$column['db'].' '.$operator.' :'.$column['db'].' ';
                            if(isset($column['select']) && $column['select'] !='' )
                                $str .= ' AND '.$prefix.'.'.$column['select'].' '.$operator.' :'.$column['select'].' ';
                        }
                        else{
                            $str .= ' OR '.$prefix.'.'.$column['db'].' '.$operator.' :'.$column['db'].' ';
                            if(isset($column['select'] )&& $column['select'] !='' )
                                $str .= ' OR '.$prefix.'.'.$column['select'].' '.$operator.' :'.$column['select'].' ';
                        }
                    }
                    elseif($column['type']!='text' && is_numeric($searchVal)){
                        if($str == ''){
                            $str .= ' AND  '.$prefix.'.'.$column['db'].' '.$operator.' :'.$column['db'].' ';
                            if(isset($column['select']) && $column['select'] !='' )
                                $str .= ' AND  '.$prefix.'.'.$column['select'].' '.$operator.' :'.$column['select'].' ';
                        }
                        else{
                            $str .= ' OR '.$prefix.'.'.$column['db'].' '.$operator.' :'.$column['db'].' ';
                            if(isset($column['select']) && $column['select'] !='' )
                                $str .= ' OR '.$prefix.'.'.$column['select'].' '.$operator.' :'.$column['select'].' ';
                        }
                    }
                }
            }
//            $str .= ' )';
            //end create query string
            $extendQuery = $this->extendSqlProcess();
            $menuQueryBuilder =    $menuQueryBuilder->where($extendQuery.$str);
            //set parameter
            foreach($columns as $column ){
                if($column['search']== true) {
                    if($column['type'] == 'text'){
                        $menuQueryBuilder = $menuQueryBuilder->setParameter($column['db'], '%'.$searchVal.'%');
                        if(isset($column['select']) && $column['select']!='')
                            $menuQueryBuilder = $menuQueryBuilder->setParameter($column['select'], '%'.$searchVal.'%');

                    }

                    elseif(is_numeric($searchVal) && $column['type']!='text'){
                        $menuQueryBuilder = $menuQueryBuilder->setParameter($column['db'],$searchVal);
                        if(isset($column['select']) && $column['select']!='')
                            $menuQueryBuilder = $menuQueryBuilder->setParameter($column['select'],$searchVal);
                    }


                }
            }
        }
        //search by column
        elseif( count($checkColumnSearch['check']) == true){

            $menuQueryBuilder = $checkColumnSearch['data'];
        }
        //ko search
        else{
            $extendQuery = $this->extendSqlProcess();
            $menuQueryBuilder = $menuQueryBuilder->where($extendQuery);
        }

        return $menuQueryBuilder;
    }
    protected function createFilterColumn($menuQueryBuilder, $request, $column, $tablePrefix){

    }
    protected function checkColumnSearch($menuQueryBuilder,$request,$tableColumns,$tablePrefix){

        $flag = false;
        $str = '';
        $requestColumns = $request['columns'];
        foreach($requestColumns as $requestColumn){
            if($requestColumn['search']['value'] != ''){
                $column = $tableColumns[$requestColumn['data']]['db'];
                if(isset($tableColumns[$requestColumn['data']]['select']) && $tableColumns[$requestColumn['data']]['select'] !='')
                {
                    $tablePrefix = $tableColumns[$requestColumn['data']]['prefix'];
                    $column = $tableColumns[$requestColumn['data']]['select'];
                }

                $type = $tableColumns[$requestColumn['data']]['type'];
                $searchVal = $requestColumn['search']['value'];
                $and = 'AND';
                if($str == ''){
                    $and = '';
                }
                if($type == 'text' ){
                    $str .= ' '.$and.' '.$tablePrefix.'.'.$column.' LIKE :'.$column.' ';
                    $menuQueryBuilder = $menuQueryBuilder->setParameter($column,'%'.$searchVal.'%');
                }
                elseif($type !='text' && is_numeric($searchVal)){
                    $str .= ' '.$and.' '.$tablePrefix.'.'.$column.' = :'.$column.' ' ;
                    $menuQueryBuilder = $menuQueryBuilder->setParameter($column,$searchVal);
                }
                $flag = true;
            }
        }
        if($str!='')
            $str = $str.' AND ';

        $extendQuery = $this->extendSqlProcess();
        $menuQueryBuilder->where($str.$extendQuery);

        return  array('check'=>$flag,'data'=>$menuQueryBuilder);
    }
    //end table ajax process

    protected function extendSqlProcess()
    {

        $str = '1=1';
        if (count($this->extendSQl) > 0) {
            foreach ($this->extendSQl as $item) {
                $str .=  ' '.$item[0].' '.$item[1].' '.$item[2].' '.$item[3];
            }
        }

        return $str;
    }
    protected function extendJoinProcess($queryBuilder){


        if(count($this->extendJoin)>0){
            foreach($this->extendJoin as $item){
                $queryBuilder =  $queryBuilder
                    ->leftJoin($item[0],$item[1],$item[2],$item[3]);
            }
        }
        return $queryBuilder;

    }
    protected function extendSelectProcess($columns){
        $returnArray = array();
        foreach($columns as $column){
            $prefix = $this->tablePrefix;
            if( isset($column['prefix'])){
                if($column['prefix']!='')
                    $prefix = $column['prefix'];
            }
            if(isset($column['select'])) {
                $returnArray[] = $prefix . '.' . $column['select'] . ' AS ' . $column['db'];
            }
        }

        return $returnArray;
    }
}