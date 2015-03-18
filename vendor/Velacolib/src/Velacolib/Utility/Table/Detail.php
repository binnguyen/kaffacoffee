<?php
namespace Velacolib\Utility\Table;


class Detail extends Table{
    protected  $detailTitle;

    /**
     * @return mixed
     */
    public function getDetailTitle()
    {
        return $this->detailTitle;
    }

    /**
     * @param mixed $detailTitle
     */
    public function setDetailTitle($detailTitle)
    {
        $this->detailTitle = $detailTitle;
    }



    public function  __construct($tableColumn,$tableData,$actionLink){
        parent::__construct($tableColumn,$tableData,$actionLink);
    }
    public function  render(){
        echo $this->contentTableHtml();
    }
    public function script(){

    }
    public function contentTableHtml(){
        $this->createLink();
        $html = '<div class="row-fluid">
                <div class="row-fluid">
                    <div class="span12 box">
                        <div class="box-header purple-background">
                            <div class="title">
                                <div class="icon-resize-horizontal"></div>
                                 '.$this->detailTitle.'             </div>
                            <div class="actions">
                                <a href="/'.$this->actionDelete.'/'.$this->tableData['id'].'" class="btn box-remove btn-mini btn-link"><i class="icon-remove"></i>
                                </a>
                                <a href="/'.$this->actionEdit.'/'.$this->tableData['id'].'" class="btn box-edit btn-mini btn-link"  data-id="121"><i class="icon-edit"></i>
                                </a>
                            </div>
                        </div>
                        <div class="box-content">
                            <form accept-charset="UTF-8" class="form form-horizontal">
                                <ul class="left-detail">';
                                foreach($this->tableColumns as $column){
                                    $html .= '<li>
                                        <p class="text-blue "><strong>'.$column['title'].'</strong></p>
                                        <p> '.$this->tableData[$column['data']].'</p>
                                    </li>';
                                    }
                                '</ul>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            ';
        return $html;
    }
}