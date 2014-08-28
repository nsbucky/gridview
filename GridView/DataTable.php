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
    public $useModalFilters = true;

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
                    <div class="widget-body-toolbar">
                        <?php echo $this->renderModalFilters();?>
                        <p class="pull-right">
                            Showing <?php echo $this->dataSource->getFrom();?>
                            to <?php echo $this->dataSource->getTo();?>
                            of <?php echo $this->dataSource->getTotal();?>
                        </p>
                    </div>
                    <table class="<?php echo $this->tableCss;?>" id="<?php echo $this->id;?>">
                        <thead>
                        <?php echo $this->renderHeader();?>
                        </thead>

                        <tbody>
                        <?php echo $this->renderTableBody();?>
                        </tbody>
                    </table>
                    <div class="dt-row dt-bottom-row">
                        <?php echo $this->dataSource->links(); ?>
                    </div>
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
            return null;
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
                    "sDom" : "R<'dt-top-row'C>r<'dt-wrapper't>>",
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

    /**
     * @return string
     */
    public function renderModalFilters()
    {

        $filters = array();

        foreach($this->columns as $c) {

            $filter = $c->getFilter();

            if( empty($filter) ) continue;

            $checked = null;

            if( $c->isVisible() ) {
                $checked = 'checked="checked"';
            }

            $filters[] = '<div class="form-group"><label>'.$c->getHeaderName().'</label>'.$filter.'</div>';
        }

        $filters = implode(PHP_EOL, $filters);

        $hidden = '';

        unset($_GET['columns']);

        foreach( $_GET as $k => $v ) {

            $k = htmlspecialchars( $k, ENT_COMPAT);

            if( is_array( $v ) ) {
                foreach( $v as $_k => $_v ) {
                    $_v = htmlspecialchars( $_v, ENT_COMPAT);

                    if( is_scalar($_k) ) {
                        $hidden .= "<input type='hidden' value='$_v' name='{$k}[$_k]'>".PHP_EOL;
                    } else {
                        $hidden .= "<input type='hidden' value='$_v' name='{$k}[]'>".PHP_EOL;
                    }

                }
                continue;
            }

            $hidden .= "<input type='hidden' value='$v' name='$k'>".PHP_EOL;

        }

        $html = <<<MODAL

<button type="button" data-toggle="modal" href="#modal-grid-filters" class="btn btn-default btn-sm"><i class="fa fa-search-plus"></i> Filter Table</button>

<!-- Modal -->
<div class="modal fade" id="modal-grid-filters" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Filter Table</h4>
            </div>
            <div class="modal-body">
                <form action="" method="get" id="grid-filter-form">
                    $hidden
                    $filters
                    <input type="reset" name="reset" value="Reset Form" class="btn btn-danger modal-reset">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary modal-save">Filter</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
    $('#modal-grid-filters .modal-save').click(function(){
        $('#grid-filter-form').submit();
    });

</script>
MODAL;

        return $html;

    }

}