<?php namespace GridView;

/**
 * Table List Class
 * The idea is for this class to easily build a 2 column table from an array,
 * useful for dumping objects
 * 
 * @author Kenrick Buchanan <nsbucky@gmail.com>
 */
class TableList extends Table {

	/**
     * the css id for the table
     * 
     * @var string
     */
	public $id            = 'GridViewList';
	
    /**
     * css classes used on the main table when rendered
     * 
     * @var string
     */
    public $tableCss      = 'table table-striped';	
    
    /**
     * the string to use for the header column
     * 
     * @var string
     */
    public $labelColumnHeader = 'Label';
    
    /**
     * the string to use for the value column
     * 
     * @var string
     */
    public $valueColumnHeader = 'Value';
	
    /**
     * Constructor
     * 
     * @param type $dataSource
     * @param array $options
     * @throws \RunTimeException on empty $dataSource
     */
	public function __construct($dataSource, array $options=null)
	{		
        $this->dataSource = $dataSource;
        
		if( empty($options) ) return;
        
        $this->setConfigOptions($options);
	}	
    
    /**
     * set public variables from an array passed as configuration array
     * 
     * @param array $options
     */
    protected function setConfigOptions(array $options, array $addAllowed=null)
    {
        $allowed = array(
            'tableCss','tableRowCss','sortUrl','itemsPerPage', 
            'itemsPerPageIdentifier', 'sortDirection','noResultsText',
            'javascript','useJqueryJavascripts', 'showItemsPerPageHeader',
            'labelColumnHeader', 'valueColumnHeader');
        
        $optionsToSet = array_intersect_key($allowed, $options);
        
        foreach($optionsToSet as $key => $value) {
            $this->$key = $value;
        }
        
    }

    /**
     * just create the row containing 2 columns, Label and Value
     * 
     * @return string
     */
	public function renderHeader() 
	{
		ob_start();
        ?>
    <tr>
        <th><?php echo $this->labelColumnHeader;?></th>
        <th><?php echo $this->valueColumnHeader;?></th>
    </tr>	
    <?php
		return ob_get_clean();
	}

    /**
     * Render an empty footer
     * 
     * @return string
     */
	public function renderFooter() 
	{	        
		ob_start();
    ?>
	<tr>
		<td></td>
		<td></td>
	</tr>	
    <?php
		return ob_get_clean();
	}
    
    /**
     * items per page is pointless for the list
     * 
     * @return null
     */
    public function renderItemsPerPage()
    {
        return null;
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

    	foreach((array) $this->dataSource as $key=>$value) {
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
		if( !empty($this->buttons) ) {
			$this->addColumn(new Columns\ButtonColumn(array('buttons'=>$this->buttons)));
		}
		ob_start();               
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
        if( empty($this->dataSource) || count($this->dataSource) == 0 ) {
            ob_start();
            ?>
        <tr>
            <td colspan="2">
                <?php echo $this->noResultsText;?>
            </td>
        </tr>
            <?php
            return ob_get_clean();
        }
        
        $rows = '';
        foreach($this->columns as $index=>$column) {            
            $rows .= $this->renderTableRow($column, $index);
        }
        return $rows;
    }
    
    public function renderTableRow($column, $index)
    {
        ob_start();
        ?>
        <tr class="<?php echo $this->getTableRowCss($this->dataSource, $index);?>">        	
            <th>
                <?php echo $column->getHeader();?>
            </th>
            <td class="<?php echo $column->cellCss;?>">
                <?php echo $column->setData($this->dataSource)->getValue($index);?>
            </td>         
        </tr>
        <?php return ob_get_clean(); 
    }

}