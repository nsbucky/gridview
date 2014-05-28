<?php namespace GridView;

use GridView\Columns\Column;

/**
 * Table Class
 * The idea is for this class to easily build a table from an array of arrays,
 * or an array of objects, something akin to what you would get back from a
 * database result set.
 *
 * @author Kenrick Buchanan <nsbucky@gmail.com>
 */
class Table implements \ArrayAccess {

    /**
     * @const SORT_DIRECTION_ASC ASC
     */
    const SORT_DIRECTION_ASC  = 'ASC';

    /**
     * @const SORT_DIRECTION_DESC DESC
     */
    const SORT_DIRECTION_DESC = 'DESC';

    /**
     * the css id for the table
     *
     * @var string
     */
    public $id            = 'GridViewTable';

    /**
     * the data source the table will be using to build the table
     *
     * @var array
     */
    public $dataSource    = array();

    /**
     * the internal array of \GridView\Columns added to the table
     *
     * @var array
     */
    protected $columns    = array();

    /**
     * css classes used on the main table when rendered
     *
     * @var string
     */
    public $tableCss      = 'table table-bordered table-striped';

    /**
     * css added to each row in the table
     *
     * @var string
     */
    public $tableRowCss   = null;

    /**
     * url used when created the sort link in javascript
     *
     * @var string
     */
    public $sortUrl       = null;

    /**
     * which way to sort the records that is sent back to server
     *
     * @var string
     */
    public $sortDirection = 'ASC';

    /**
     * Collection of headers taken from the added columns
     *
     * @var array
     */
    protected $headers    = array();

    /**
     * Collection of footers taken from the added columns
     *
     * @var string
     */
    protected $footers    = array();

    /**
     * message to display when the $dataSource is empty
     *
     * @var string
     */
    public $noResultsText = '<p><span class="text-warning">No results.</span></p>';

    /**
     * javascript that will be included at end of table
     *
     * @var string
     */
    public $javascript    = '';

    /**
     * If true, will use the generated jQuery based javascripts for table functionality
     * like sorting and items per page
     *
     * @var boolean
     */
    public $useJqueryJavascripts = true;

    /**
     * number of items to render per page. the table does not limit the amount
     * of items to display, but rather will pass this value back to server via
     * url using the $itemsPerPageIdentifier. This array is used to build a drop
     * down list of options
     *
     * @var array
     */
    public $itemsPerPage  = array(10, 20, 50, 100);

    /**
     * the identifier used for building the drop down list of $itemsPerPag
     *
     * @var string
     */
    public $itemsPerPageIdentifier = 'limit';

    /**
     * turn on/off the display of $itemsPerPage drop down list
     *
     * @var boolean
     */
    public $showItemsPerPageHeader = false;

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
    public $noFilters       = false;

    /**
     * @var bool
     */
    public $useColumnFilters = false;

    /**
     * use the modal filter by default. the column headers are freaking annoying as fuck.
     * @var bool
     */
    public $useModalFilters = true;

    /**
     * function to determine column visibility, like from session?
     * @callable
     */
    public static $visibleColumnsCallback;

    /**
     * @var array
     */
    protected $visibleColumns = array();

    /**
     * Constructor
     *
     * @param type $dataSource
     * @param array $options
     * @throws \RunTimeException on $emptyDataSource
     */
    public function __construct($dataSource, array $options=array())
    {
        $this->dataSource = $dataSource;
        $this->setConfigOptions($options);

        if( is_callable( self::$visibleColumnsCallback ) ) {
            $callback = self::$visibleColumnsCallback;
            $this->visibleColumns = $callback($this->id);
        }
    }

    /**
     * magic method for adding button types like addCalcColumn()
     *
     * @param mixed $name
     * @param array $arguments
     * @throws \BadFunctionCallException
     * @return GridView
     */
    public function __call( $name, $arguments )
    {
        $add = substr($name, 0, 3);
        if( $add == 'add' ) {

            $class = substr($name, 3);

            $class = "\\GridView\\Columns\\".$class;

            // if arg is just a string, create an array.
            $arg = $arguments[0];
            if( is_string($arg) ) {
                $arg = array('name'=>$arg);
            }

            return $this->addColumn( new $class($arg) );

        }

        throw new \BadFunctionCallException('No method named '.$name.' exists');
    }

    /**
     * set public variables from an array passed as configuration array
     *
     * @param array $options
     */
    protected function setConfigOptions(array $options)
    {
        $allowed = array(
            'tableCss','tableRowCss','sortUrl','itemsPerPage', 'noFilters',
            'itemsPerPageIdentifier', 'sortDirection','noResultsText',
            'javascript','useJqueryJavascripts', 'showItemsPerPageHeader',
            'id','useModalFilters','useColumnFilters'
        );

        $optionsToSet = array_intersect( $allowed, array_keys($options) );

        foreach($optionsToSet as $key) {
            $this->$key = $options[$key];
        }
    }

    /**
     * add elements to table via array access
     *
     * @param mixed $id
     * @param string $value
     */
    public function offsetSet($id, $value)
    {
        if(is_null($id)) {
            $this->addColumn($value);
            return;
        }

        $this->columns[$id] = $value;
    }

    /**
     * retrieve a column from this table via array access
     *
     * @param mixed $id
     * @return \GridView\Columns\Column
     * @throws \Exception
     */
    public function offsetGet($id)
    {
        if (!array_key_exists($id, $this->columns)) {
            throw new \Exception(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->columns[$id];
    }

    /**
     * is identifier set in columns array
     *
     * @param mixed $id
     * @return boolean
     */
    public function offsetExists($id)
    {
        return array_key_exists($id, $this->columns);
    }

    /**
     * unset column by identifier
     *
     * @param mixed $id
     */
    public function offsetUnset($id)
    {
        unset($this->columns[$id]);
    }

    /**
     * generate and retrieve the url with sort parameters using the $sortDirection
     * and $sortUrl for given column $name
     *
     * @param string $column
     * @return string
     */
    public function getSortUrl($column)
    {
        $this->sortDirection = $this->getSortDirectionForColumn($column);

        // append with get stuff.
        $qs = array_merge($_GET,  array('sort'=>$column->sortableName, 'sort_dir'=>$this->sortDirection));

        $query = http_build_query( $qs );

        if(strpos($this->sortUrl, '?') !== false) {
            return $this->sortUrl .= '&'.$query;
        }

        return $this->sortUrl.'?'.$query;
    }

    /**
     * Get sort direction for column based on query string
     *
     * @param string $column
     * @return string
     */
    public function getSortDirectionForColumn($column)
    {
        $sortDirection = $column->sortDirection ?: self::SORT_DIRECTION_ASC;

        if( isset( $_GET['sort']) && $_GET['sort'] == $column->sortableName ) {
            $sortDirection = (isset($_GET['sort_dir'] ) &&  $_GET['sort_dir'] == self::SORT_DIRECTION_ASC)
                ? self::SORT_DIRECTION_DESC
                : self::SORT_DIRECTION_ASC;
        }

        return $sortDirection;
    }

    /**
     * add column to table. If a string is passed as column, that name will be
     * used for the $name property of the column, and a GridView\Columns\Column
     * instance will be created.
     * @param mixed $column
     * @return \GridView\Table
     */
    public function addColumn($column)
    {
        if(is_array($column)) {
            $column = new Columns\Column($column);
        }

        if(is_scalar($column)) {
            $column = new Columns\Column(array('name'=>$column));
        }

        $column->setTable($this);

        $this->javascript .= $column->getJavaScript();

        // if column is not meant to be visible, like for admin reasons, take it out of the visible columns
        if( ! $column->isVisible() ) {
            $flip = array_flip($this->visibleColumns);
            unset($flip[$column->name]);
            $this->visibleColumns = $flip;
        }

        if( ! $this->noFilters && $this->visibleColumns != false && ! in_array($column->name, $this->visibleColumns)) {
            $column->visible = false;
        }

        if( $column->isVisible() ) {
            $this->headers[] = 1;
        }

        $this->columns[]   = $column;

        $this->setTableFooter($column);
        return $this;
    }

    /**
     * add column to footers array if the $name property is set in the column
     * and if not just add a null value to footer to account for column count
     *
     * @param \GridView\Columns\Column $column
     */
    protected function setTableFooter($column)
    {
        if( $column->isVisible() ) {
            $this->footers[$column->name] = $column->getFooter();
        }
    }

    /**
     * create the string of html for the table header row and filter row
     *
     * @return string
     */
    public function renderHeader()
    {
        ob_start();
        $headers = array();
        $filters = array();
        foreach($this->columns as $c):
            if( ! $c->isVisible() ) continue;
            $headers[] = $c->getHeader();
            $filters[] = $c->getFilter();
        endforeach;
        ?>
        <tr class="grid-view-headers">
            <th><?php echo implode("\n</th>\n<th>\n", $headers);?></th>
        </tr>
        <?php if( ! $this->useColumnFilters ) return ob_get_clean();?>
        <tr class="grid-view-filters">
            <th><?php echo implode("\n</th>\n<th>\n", $filters);?></th>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function renderModalFilters()
    {
        $this->useColumnFilters = false;

        if( $this->noFilters ) return ;

        $filters = array();

        foreach($this->columns as $c) {

            $filter = $c->getFilter();

            if( empty($filter) ) continue;

            $checked = null;

            if( $c->isVisible() ) {
                $checked = 'checked="checked"';
            }

            $filters[] = '<div class="form-group">
                            <label class="checkbox">
                            <input type="checkbox" name="columns['.$c->name.']" value="1" '.$checked.'>
                            '.$c->getHeaderName().'</label>'.$filter.'</div>';
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

<button type="button" data-toggle="modal" href="#modal-grid-filters" class="btn btn-success"><i class="fa fa-search-plus"></i> Filter Table</button>

<!-- Modal -->
<div class="modal fade" id="modal-grid-filters" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Filter Table</h4>
            </div>
            <div class="modal-body">
                <label><input type="checkbox" class="select-all-fields">Select All</label>
                <form action="" method="get" id="grid-filter-form">
                    $hidden
                    $filters
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
    $('.select-all-fields').click(function(){
        var checks = $('.modal-body :checkbox');
        checks.prop("checked", $(this).is(':checked'));
    });
</script>
MODAL;

        return $html;

    }

    /**
     * create the the string of html for the table footer. It will inspect each
     * element of the footer array to see if it has a callable function in the
     * format key, and format the value key using that function, otherwise it will
     * not print anything to the column
     *
     * @return string
     */
    public function renderFooter()
    {
        if(empty($this->footers)) return;

        ob_start();
        ?>
        <tr>
            <?php foreach($this->footers as $f): ?>
                <td><?php
                    if($f['value'] && $f['format']):
                        $func = $f['format'];
                        if(is_callable($func)) echo $func($f['value']);
                    endif;
                    ?></td>
            <?php endforeach;?>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * create the string of html for the drop down list using the $itemsPerPage
     * variable. It also creates some boilerplate javascript that depends on jQuery
     * to build a url to send to server.
     *
     * @return string
     */
    public function renderItemsPerPage()
    {
        ob_start()
        ?>
        <div class="row">
            <div class="pull-right col-md-12">
                Showing <?php echo count($this->dataSource);?> items.
                <?php
                if(is_array($this->itemsPerPage)) {
                    ?>
                    <select name="<?php echo $this->itemsPerPageIdentifier?>"
                            id="grid-view-<?php echo $this->itemsPerPageIdentifier?>">
                        <?php
                        foreach($this->itemsPerPage as $limit) {
                            printf('<option value="%d">%d</option>'.PHP_EOL, $limit, $limit);
                        }
                        ?>
                    </select>
                <?php
                }
                ?>
            </div>
        </div>

        <?php
        if( !$this->useJqueryJavascripts ) return ob_get_clean();

        ob_start();
        ?>
        <script>
            jQuery(function(){
                $("#grid-view-<?php echo $this->itemsPerPageIdentifier?>").change(function(){
                    var values = $('#<?php echo $this->id;?> .grid-view-filters :input').serialize();
                    window.location = window.location.pathname+'?'+values+"&<?php echo $this->itemsPerPageIdentifier?>="+$(this).val();
                });
            });
        </script>
        <?php $this->javascript .= ob_get_clean();?>
        <?php
        return ob_get_clean();
    }

    /**
     * if no columns have been added, try to come up with a table from just the columns
     * in the result set. It will use the first entry in the $dataSource array
     * to get a sample of keys=>values to use
     *
     */
    public function buildDefaultColumns()
    {
        if(count($this->columns) > 0) return;

        $dataSample = $this->dataSource[0];

        foreach((array) $dataSample as $key=>$value) {
            $this->addColumn(array('name'=>$key));
        }
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

        if($this->showItemsPerPageHeader) echo $this->renderItemsPerPage();

        if( $this->useModalFilters ) echo $this->renderModalFilters();
        ?>
        <table class="<?php echo $this->tableCss;?>" id="<?php echo $this->id;?>">
            <thead>
            <?php echo $this->renderHeader();?>
            </thead>

            <tbody>
            <?php echo $this->renderTableBody();?>
            </tbody>

            <tfoot>
            <?php echo $this->renderFooter();?>
            </tfoot>
        </table>

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
     * call the render() method when table instance is being used in a string
     * context
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Get the row css to use for the table row. If the $tableRowCss is a callable
     * function, use the data set from the current row of $dataSource to help
     * evaluate what string of css should be returned.
     *
     * @param mixed $data
     * @param integer $index
     * @return string
     */
    public function getTableRowCss($data, $index)
    {
        if( is_callable($this->tableRowCss) ) {
            $func = $this->tableRowCss;
            return $func($data);
        }

        return (string) $this->tableRowCss;
    }

    /**
     * build javascript to be inserted after table if $useJQueryJavascripts is
     * set to true
     *
     * @return string
     */
    public function javaScript()
    {
        if( $this->useJqueryJavascripts && $this->useColumnFilters == true ) {
            // get default javascript for this table
            ob_start();
            ?>
            <script>
                jQuery(function(){
                    $('#<?php echo $this->id;?> .grid-view-filters :input').not('.grid-view-select-all').blur(function(){
                        // serialize the inputs and submit a form
                        var values = $('#<?php echo $this->id;?> .grid-view-filters :input').serialize();
                        window.location = window.location.pathname+'?'+values;
                    });
                });
            </script>
            <?php
            $this->javascript .= ob_get_clean();
        }
        return $this->javascript;
    }

    /**
     * get the $footers value for key $name
     *
     * @param string $name
     * @return string
     */
    public function getFooterValue($name)
    {
        if(array_key_exists($name, $this->footers)) {
            return $this->footers[$name]['value'];
        }
    }

    /**
     * set footer value in $footers array
     *
     * @param string $name
     * @param string $value
     */
    public function setFooterValue($name, $value)
    {
        if(array_key_exists($name, $this->footers)) {
            $this->footers[$name]['value'] = $value;
        }
    }

    /**
     * Convenience function for quickly added a button with a label of View
     * @param string $url
     * @return \GridView\Table
     */
    public function addViewButton($url)
    {
        $this->buttons[] = new Buttons\ViewButton($url);
        return $this;
    }

    /**
     * Convenience function for quickly added a button with a label of Edit
     * @param string $url
     * @return \GridView\Table
     */
    public function addEditButton($url)
    {
        $this->buttons[] = new Buttons\EditButton($url);
        return $this;
    }

    /**
     * Convenience function for quickly added a button with a label of Delete
     *
     * @param string $url
     * @return \GridView\Table
     */
    public function addDeleteButton($url)
    {
        $this->buttons[] = new Buttons\DeleteButton($url);
        return $this;
    }
}