<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 3/18/2015
 * Time: 3:31 PM
 */
namespace Velacolib\Utility\Table;


use Velacolib\Utility\Utility;


class AjaxTableSum extends AjaxTable{

    protected $sumOption;
    protected $sumColumn;
    /**
     * @return array
     */
    public function getSumOption()
    {
        return $this->sumOption;
    }

    /**
     * @param array $sumOption
     */
    public function setSumOption($sumOption)
    {
        $this->sumOption = $sumOption;
    }

    /**
     * @return array
     */
    public function getSumColumn()
    {
        return $this->sumColumn;
    }

    /**
     * @param array $sumColumn
     */
    public function setSumColumn($sumColumn)
    {
        $this->sumColumn = $sumColumn;
    }




    public function __construct(){
        $config = Utility::getConfig();
        $this->sumOption =  array(
            'option' =>
                array(
                    'curency' =>$config['currency'],
                    'currency_before' => $config['currency_before'],
                    'number_decimal' => $config['number_decimal']
                )
        );
        $this->sumColumn = array();
        parent::__construct();
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
                var sumColumns = <?php echo json_encode($this->sumColumn); ?>;
                var sumOption = <?php echo json_encode($this->sumOption); ?>;
                var table = $(selecttion +'').DataTable( {
                    "autoWidth" : true,
                    "processing": true,
                    "serverSide": true,
                    "ajax": "<?php echo $this->ajaxCall; ?>",
//                    stateSave: true,
                    "language": lang,
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    //render action
                    "order": [[ <?php echo $this->sortColumn ?>, '<?php echo $this->sortOrder ?>' ]],
                    "fnDrawCallback" : function() {
                        var api = this.api();
                        console.log(sumColumns);
                        var totalVal = 0;
                        for(var i=0;i<sumColumns.length;i++){
                            totalVal = totalColumn(api,sumColumns[i],sumOption);
                            //render footer
                            console.log(sumColumns[i] );
                            $( api.column( sumColumns[i] ).footer() ).html(totalVal);

                        }

                    }
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
            function totalColumn(api,$col,$sumOption){
                console.log($sumOption);
                var totalItems = api.column($col).data();
                var totalVal = 0;
                for(var i=0;i<totalItems.length;i++){
                    var item =  totalItems[i];
                    item = item.replace(',','');
                    item = item.replace($sumOption.option.curency,'');
                    totalVal += parseFloat(item);
                }
                var curency = $sumOption.option.curency;
                var before = $sumOption.option.currency_before;
                var decimal = $sumOption.option.number_decimal;
                totalVal = parseFloat(totalVal).toFixed(decimal);
                totalVal = addCommas(totalVal);
                if(before ==1 )
                    totalVal = curency+' '+totalVal;
                else
                    totalVal = totalVal + curency;

                return totalVal;
            }

            function addCommas(nStr)
            {
                nStr += '';
                x = nStr.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ',' + '$2');
                }
                return x1 + x2;
            }
        </script>
    <?php
    }

}