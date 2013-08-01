<?php namespace GridView;

use Columns\Column;

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
     * Collection of footeres taken from the added columns
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
     * Constructor
     * 
     * @param type $dataSource
     * @param array $options
     * @throws \RunTimeException on $emptyDataSource
     */
	public function __construct($dataSource, array $options=null)
	{		
		if( empty($dataSource) ) {
			throw new \RunTimeException("Datasource must not be an empty array");
		}

		$this->dataSource = $dataSource;
        
        if( empty($options) ) return;
        
        $this->setConfigOptions($options);
        
	}
    
    /**
     * set public variables from an array passed as configuration array
     * 
     * @param array $options
     */
    protected function setConfigOptions(array $options)
    {        
        $allowed = array(
            'tableCss','tableRowCss','sortUrl','itemsPerPage', 
            'itemsPerPageIdentifier', 'sortDirection','noResultsText',
            'javascript','useJqueryJavascripts', 'showItemsPerPageHeader'
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
     * @return GridView\Columns\Column
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
     * @param string $name
     * @return string
     */
	public function getSortUrl($name)
	{        
        $this->sortDirection = $this->getSortDirectionForColumn($name);
        
        $query = http_build_query(array('sort'=>$name, 'sort_dir'=>$this->sortDirection));
        
        if(strpos($this->sortUrl, '?') !== false) {
            return $this->sortUrl .= '&'.$query;
        }
        
		return $this->sortUrl.'?'.$query;
	}
    
    /**
     * Get sort direction for column based on query string
     * 
     * @param string $column
     */
    public function getSortDirectionForColumn($column)
    {
        $sortDirectionSet = isset($_GET['sort_dir']) && $_GET['sort_dir'];
        $sortColumnSet = isset($_GET['sort']) && $_GET['sort'];
                
        if(!$sortDirectionSet) return self::SORT_DIRECTION_ASC;
        if(!$sortColumnSet) return self::SORT_DIRECTION_ASC;
        
        return ($_GET['sort_dir'] === self::SORT_DIRECTION_ASC) 
               ? self::SORT_DIRECTION_DESC
               : self::SORT_DIRECTION_ASC;
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

		if(!$column->isVisible()) return;

		$header = array(
			'value' => $column->getHeader(),
			'filter' => $column->getFilter(),
		);

		if($column->sortable) {
			$header['value'] = sprintf('<a href="%s" class="sort-link">%s</a>', 
									   $this->getSortUrl($column->name),
									   $header['value']
									   );
		}

		$this->javascript .= $column->getJavaScript();
		$this->headers[]   = $header;
		$this->columns[]   = $column;
        
        $this->setTableFooter($column);
		return $this;
	}
    
    /**
     * add column to footers array if the $name property is set in the column
     * and if not just add a null value to footer to account for column count
     * 
     * @param GridView\Columns\Column $column
     */
    protected function setTableFooter($column)
    {
        if(isset($column->name)) {			
			$this->footers[$column->name] = $column->getFooter();
            return;
		}
		
        $this->footers[] = null;		
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
        foreach($this->headers as $h):
            $headers[] = $h['value'];
            $filters[] = $h['filter'];
        endforeach;		
	?>
	<tr class="grid-view-headers">
		<th><?php echo implode("\n</th>\n<th>\n", $headers);?></th>
	</tr>
	<tr class="grid-view-filters">
		<th><?php echo implode("\n</th>\n<th>\n", $filters);?></th>
	</tr>
    <?php
		return ob_get_clean();
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
<div class="row-fluid">
    <div class="pull-right span3">
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
jQuery(function(){
	$("#grid-view-<?php echo $this->itemsPerPageIdentifier?>").change(function(){
	        var values = $('#<?php echo $this->id;?> .grid-view-filters :input').serialize();
			window.location = window.location.pathname+'?'+values+"&<?php echo $this->itemsPerPageIdentifier?>="+$(this).val();
	});
});
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
			$this->addColumn(new Columns\ButtonColumn(array('buttons'=>$this->buttons)));
		}
		ob_start();
        
        if($this->showItemsPerPageHeader) echo $this->renderItemsPerPage();
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
        if( empty( $this->dataSource ) ) {
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
     * @param type $data
     * @param type $index
     * @return string
     */
    public function renderTableRow($data, $index)
    {
        ob_start();
        ?>
        <tr class="<?php echo $this->getTableRowCss($data, $index);?>">
        <?php foreach($this->columns as $column) { ?>	
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
     * @param type $data
     * @param type $index
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
        if( !$this->useJqueryJavascripts ) return;
		// get default javascript for this table		
		ob_start();
		?>
<script>
jQuery(function(){
	$('#<?php echo $this->id;?> .grid-view-filters :input').blur(function(){
		// serialize the inputs and submit a form
		var values = $('#<?php echo $this->id;?> .grid-view-filters :input').serialize();
		window.location = window.location.pathname+'?'+values;
	});
});
</script>		
		<?php		
		$this->javascript .= ob_get_clean();
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
     * @param type $name
     * @param type $value
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