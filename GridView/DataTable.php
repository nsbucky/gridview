<?php namespace GridView;

/**
 * DataTable Class
 * Create table compatible with jQuery DataTables script
 * @author Kenrick Buchanan <nsbucky@gmail.com>
 */
class DataTable extends Table {

    /**
     * the css id for the table
     *
     * @var string
     */
    public $id            = 'GridViewDataTable';

    /**
     * css classes used on the main table when rendered
     *
     * @var string
     */
    public $tableCss      = 'table table-striped table-bordered table-hover';

    /**
     * If true, will use the generated jQuery based javascripts for table functionality
     * like sorting and items per page
     *
     * @var boolean
     */
    public $useJqueryJavascripts = true;

    /**
     * Collection of buttons added via the addEditButton(), addViewButton, and
     * addDeleteButton() methods. Those convenience methods bypass normal column
     * creation, and this groups them all together into the same column, which is
     * usually what you want for the table
     *
     * @var array
     */
    protected $buttons = array();

    /**
     * if set to true, the filters row in the header will be turned off.
     *
     * @var boolean
     */
    public $noFilters       = true;

    /**
     * @var bool
     */
    public $useColumnFilters = false;

    /**
     * use the modal filter by default. the column headers are freaking annoying as fuck.
     * @var bool
     */
    public $useModalFilters = false;

    public $headerText = 'Viewing All';

    /**
     * create the string of html for the table header row and filter row
     *
     * @return string
     */
    public function renderHeader()
    {
        ob_start();
        $headers = array();
        foreach($this->columns as $c):
            if( ! $c->isVisible() ) continue;
            $headers[] = $c->getHeader();
        endforeach;
        ?>
        <tr class="grid-view-headers">
            <th><?php echo implode("\n</th>\n<th>\n", $headers);?></th>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * build the whole table based on the $dataSource. It will first try to build
     * any default columns based on first. Also if the $buttons array is not empty
     * it will add a GridView\Columns\ButtonColumn column to the end of the $columns array
     *
     * @return string
     */
    public function render()
    {
        $this->buildDefaultColumns();

        // add in auto button columns
        if( !empty( $this->buttons ) ) {

            $column = new Columns\ButtonColumn(array('buttons'=>$this->buttons));
            $this->visibleColumns[] = $column->name;
            $this->addColumn( $column );
        }

        ob_start();

        ?>
        <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                <h2><?php echo $this->headerText;?></h2>
            </header>
            <div>
                <div class="jarviswidget-editbox"></div>
                <div class="widget-body no-padding">
                    <div class="widget-body-toolbar"></div>
                    <table class="<?php echo $this->tableCss;?>" id="<?php echo $this->id;?>">
                        <thead>
                        <?php echo $this->renderHeader();?>
                        </thead>

                        <tbody>
                        <?php echo $this->renderTableBody();?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php echo $this->javaScript();?>

        <?php
        return ob_get_clean();
    }

    /**
     * render the tbody section
     *
     * @return string
     */
    public function renderTableBody()
    {
        // if data source is empty show the $noResults text
        if( empty( $this->dataSource ) || count($this->dataSource) == 0 ) {
            ob_start();
            ?>
            <tr>
                <td colspan="<?php echo count($this->headers);?>"><?php echo $this->noResultsText;?></td>
            </tr>
            <?php
            return ob_get_clean();
        }

        $rows = '';
        foreach($this->dataSource as $index=>$data) {
            $rows .= $this->renderTableRow($data, $index);
        }
        return $rows;
    }

    /**
     * REnder a table row
     *
     * @param mixed $data
     * @param integer $index
     * @return string
     */
    public function renderTableRow($data, $index)
    {
        ob_start();
        ?>
        <tr class="<?php echo $this->getTableRowCss($data, $index);?>">
            <?php foreach($this->columns as $column) { ?>
                <?php if( ! $column->isVisible() ) continue; ?>
                <td class="<?php echo $column->cellCss;?>">
                    <?php echo $column->setData($data)->getValue($index);?>
                </td>
            <?php } ?>
        </tr>
        <?php return ob_get_clean();
    }

    /**
     * build javascript to be inserted after table if $useJQueryJavascripts is
     * set to true
     *
     * @return string
     */
    public function javaScript()
    {

        // get default javascript for this table
        ob_start();
        ?>
        <script>
            jQuery(function(){
                $('#<?php echo $this->id?>').dataTable({
                    "sDom" : "R<'dt-top-row'Clf>r<'dt-wrapper't><'dt-row dt-bottom-row'<'row'<'col-sm-6'i><'col-sm-6 text-right'p>>",
                    "sPaginationType" : "bootstrap_full",
                    "bStateSave": true,
                    "bSortCellsTop" : true,
                    "fnInitComplete" : function(oSettings, json) {
                        $('.ColVis_Button').addClass('btn btn-default btn-sm').html('Columns <i class="icon-arrow-down"></i>');
                    }
                });
            });
        </script>
        <?php
        $this->javascript .= ob_get_clean();

        return $this->javascript;
    }

}