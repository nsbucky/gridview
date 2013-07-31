<?php namespace GridView;

class TableList extends Table {
	public $id            = 'GridViewList';
	public $dataSource    = array();
	protected $columns    = array();
	public $tableCss      = 'table table-striped';	
	public $tableRowCss   = null;		
	protected $headers    = array();	
	public $noResultsText = '<p><span class="text-warning">No results.</span></p>';
	public $javascript    = '';		    
	protected $buttons = array();

	public function __construct($dataSource, array $options=null)
	{		
		if(is_null($dataSource)) {
			throw new \RunTimeException("Datasource must not be an empty");
		}

		parent::__construct($dataSource, $options);
	}	

	public function renderHeader() 
	{
		ob_start();
?>
<thead>
	<tr>
		<th>Label</th>
		<th>Value</th>
	</tr>	
</thead>
<?php
		return ob_get_clean();
	}

	public function renderFooter() 
	{		
		if(empty($this->footers)) return;

		ob_start();
?>
<tfoot>	
	<?php foreach($this->footers as $f): ?>
	<tr>
		<td></td>
		<td><?php         
		if($f['value'] && $f['format']):
			$func = $f['format'];
			if(is_callable($func)) echo $func($f['value']);
		endif;		
		?></td>
	</tr>
	<?php endforeach;?>	
</tfoot>
<?php
		return ob_get_clean();
	}
    
    public function renderItemsPerPage()
    {
        return null;
    }

    /**
     * if no columns have been added, try to come up with a table from just the columns
     * in the result set.
     */
    public function buildDefaultColumns()
    {
    	if(count($this->columns) > 0) return;    	    	

    	foreach((array) $this->dataSource as $key=>$value) {
    		$this->addColumn(array('name'=>$key));
    	}
    }

	public function render()
	{
		$this->buildDefaultColumns();
		// add in auto button columns
		if(count($this->buttons)) {
			$this->addColumn(new Columns\ButtonColumn(array('buttons'=>$this->buttons)));
		}
		ob_start();               
?>
<table class="<?php echo $this->tableCss;?>" id="<?php echo $this->id;?>">
	<?php echo $this->renderHeader();?>	
	<tbody>
<?php 
if(!empty($this->dataSource)):		
	?>
	
	<?php
		foreach($this->columns as $column):
	?>	
	<tr>
		<th><?php echo $column->getHeader();?></th>
		<td class="<?php echo $column->cellCss;?>"><?php echo $column->setData($this->dataSource)->getValue(0);?></td> 
	</tr>
<?php
		endforeach;
else:
?>
	<tr>
		<td colspan="<?php echo count($this->headers);?>"><?php echo $this->noResultsText;?></td>
	</tr>
<?php
endif;
?>	
	</tbody>
	<?php echo $this->renderFooter();?>
</table>

<?php echo $this->javaScript();?>

<?php
		return ob_get_clean();
	}

	public function __toString()
	{
		return $this->render();
	}

	public function javaScript()
	{
		return $this->javascript;
	}

	public function addViewButton($url)
	{
		$this->buttons[] = new Buttons\ViewButton($url);
		return $this;
	}

	public function addEditButton($url)
	{
		$this->buttons[] = new Buttons\EditButton($url);
		return $this;
	}

	public function addDeleteButton($url)
	{
		$this->buttons[] = new Buttons\DeleteButton($url);
		return $this;
	}
}