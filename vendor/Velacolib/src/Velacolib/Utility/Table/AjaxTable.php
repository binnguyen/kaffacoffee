<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 11/20/2014
 * Time: 10:38 AM
 */
namespace Velacolib\Utility\Table;

class AjaxTable extends Table{
    protected  $ajaxCall;
    /**
     * @return mixed
     */
    public function getAjaxCall()
    {
        return $this->ajaxCall;
    }
    /**
     * @param mixed $ajaxCall
     */
    public function setAjaxCall($ajaxCall)
    {
        $this->ajaxCall = $ajaxCall;
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
                var table = $(selecttion +'').DataTable( {
                    "autoWidth" : true,
                    "processing": true,
                    "serverSide": true,
                    "ajax": "<?php echo $this->ajaxCall; ?>",
//                    stateSave: true,
                    "language": lang,
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    //render action
                    "order": [[ <?php echo $this->sortColumn ?>, '<?php echo $this->sortOrder ?>' ]]
                    //"order": [[0, 'desc' ]]
                    //end render action
                });

                //Apply the search
                table.columns().eq( 0 ).each( function ( colIdx ) {
                    $( '.column_filter', table.column( colIdx ).footer() ).on( 'keyup change', function () {
                        console.log(this.value);
                        table
                            .column( colIdx )
                            .search( this.value )
                            .draw();
                    } );
                } );

                _table = table;
            } );
        </script>
    <?php
    }
    public function contentTableHtml(){
        $actionAddButton = ' <a class="btn btn-secondary" href="/'.$this->actionAdd.'">'.$this->buttonTextAdd.'</a>';
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
        <div class="panel-body overflow-x-scroll">
            <div class="table-responsive">';
        $html .= '<table id="'.str_replace('#','',$this->selecttion).'" class="table table-striped table-bordered dataTable table-responsive" cellspacing="0"  >';
        $html .= '<thead>';
        foreach($this->tableColumns as $colum){
            $html .=    '<th>'.$colum['title'].'</th>';
        }
        $html .= '</thead>';
        $html .= '<tfoot>';
        $html.= $this->renderColumn();
        $html .= '</tfoot>';
        $html .='</table>
                </div>
            </div>
        </div>';
        return $html;
    }
    private function renderColumn(){
        $html ='';
        foreach($this->tableColumns as $colum){
            $select = isset($colum['dataSelect'])?$colum['dataSelect']:array();
            if(count($select)>0){
                $html .= '<th>';
                $html .= '<select class="column_filter">';
                $html .= '<option></option>';
                foreach($colum['dataSelect'] as $k => $val){
                    $html .= '<option value="'.$k.'">'.$val.'</option>';
                }
                $html .= '</select>';
                $html .= '</th>';
            }
            else{
                $html .=    '<th><input type="text" class="column_filter" type="text"> </th>';
            }
        }
        return $html;
    }
}