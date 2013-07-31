<?php namespace GridView;

use Columns\Column;

class Table implements \ArrayAccess {	

	public $id            = 'GridViewTable';
	public $dataSource    = array();
	protected $columns    = array();
	public $tableCss      = 'table table-bordered table-striped';	
	public $tableRowCss   = null;	
	public $sortUrl       = null;
	public $sortDirection = 'ASC';
	protected $headers    = array();
	protected $footers    = array();
	public $noResultsText = '<p><span class="text-warning">No results.</span></p>';
	public $javascript    = '';	
	public $itemsPerPage  = array(10, 20, 50, 100);
    public $itemsPerPageIdentifier = 'limit';
	public $showItemsPerPageHeader = false;
	protected $buttons = array();

	public function __construct($dataSource, array $options=null)
	{		
		$this->dataSource = $dataSource;
        
        if(count($options)) {
            $allowed = array('tableCss','tableRowCss','sortUrl','itemsPerPage', 'itemsPerPageIdentifier',
                             'sortDirection','noResultsText','javascript','showItemsPerPageHeader');
            foreach($options as $key=>$value) {
                if(!in_array($key, $allowed)) continue;
                $this->$key = $value;
            }
        }
	}

	public function offsetSet($id, $value)
    {
    	$this->addColumn($value);
        #$this->columns[$id] = $value;
    }

    public function offsetGet($id)
    {
        if (!array_key_exists($id, $this->columns)) {
            throw new \Exception(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->attributes[$id];
    }

    public function offsetExists($id)
    {
        return array_key_exists($id, $this->columns);
    }

    public function offsetUnset($id)
    {
        unset($this->columns[$id]);
    }

	public function getSortUrl($name)
	{
		if(isset($_GET['sort']) && $_GET['sort'] == $name) {
			// flip the direction
			if(isset($_GET['sort_dir']) && $_GET['sort_dir'] == "ASC") {
				$this->sortDirection = "DESC";
			}
            
            if(isset($_GET['sort_dir']) && $_GET['sort_dir'] == "DESC") {
				$this->sortDirection = "ASC";
			}
		}
        
        $query = http_build_query(array('sort'=>$name, 'sort_dir'=>$this->sortDirection));
        
        if(strpos($this->sortUrl, '?') !== false) {
            return $this->sortUrl .= '&'.$query;
        }
        
		return $this->sortUrl.'?'.$query;
	}

	public function addColumn($column)
	{
		if(is_scalar($column)) {
			$column = new Columns\Column(array('name'=>$column));
		} else if(is_array($column)) {
			$column = new Columns\Column($column);
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
		$this->headers[] = $header;
		$this->columns[] = $column;
		if(isset($column->name)) {			
			$this->footers[$column->name] = $column->getFooter();
		} else {						
			$this->footers[] = null;
		}

		return $this;
	}

	public function renderHeader()
	{
		ob_start();
?>
<thead>
	<?php
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
</tfoot>
<?php
		return ob_get_clean();
	}
    
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

<?php ob_start();?>
jQuery(function(){
	$("#grid-view-<?php echo $this->itemsPerPageIdentifier?>").change(function(){
	        var values = $('#<?php echo $this->id;?> .grid-view-filters :input').serialize();
			window.location = window.pathname+'?'+values+"&<?php echo $this->itemsPerPageIdentifier?>="+$(this).val();
	});
});
<?php $this->javascript .= ob_get_clean();?>
<?php
        return ob_get_clean();
    }

	public function render()
	{
		// add in auto button columns
		if(count($this->buttons)) {
			$this->addColumn(new Columns\ButtonColumn(array('buttons'=>$this->buttons)));
		}
		ob_start();
        
        if($this->showItemsPerPageHeader) echo $this->renderItemsPerPage();
?>
<table class="<?php echo $this->tableCss;?>">
	<?php echo $this->renderHeader();?>	
	<tbody>
<?php 
if(count($this->dataSource)):
	$index = 0;
	foreach($this->dataSource as $index=>$data):
	?>
	<tr class="<?php echo $this->getTableRowCss($data, $index);?>">
	<?php
		foreach($this->columns as $column):
	?>	
		<td class="<?php echo $column->cellCss;?>"><?php echo $column->setData($data)->getValue($index);?></td> 
<?php
		endforeach;
	?>
	</tr>
	<?php
	$index++;
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

	public function getTableRowCss($data, $index)
	{
		if(is_callable($this->tableRowCss)) {
			$func = $this->tableRowCss;
			return $func($data);
		}

		return (string) $this->tableRowCss;
	}

	public function javaScript()
	{
		// get default javascript for this table
		// sorry it assumes jquery.
		ob_start();
		?>
<script>
jQuery(function(){
	$('#<?php echo $this->id;?> .grid-view-filters :input').blur(function(){
		// serialize the inputs and submit a form
		var values = $('#<?php echo $this->id;?> .grid-view-filters :input').serialize();
		window.location = window.pathname+'?'+values;
	});
});
</script>		
		<?php		
		$this->javascript .= ob_get_clean();
		return $this->javascript;
	}

	public function getFooterValue($name)
	{
		if(array_key_exists($name, $this->footers)) {
			return $this->footers[$name]['value'];
		}
	}

	public function setFooterValue($name, $value)
	{
		if(array_key_exists($name, $this->footers)) {
			$this->footers[$name]['value'] = $value;
		}
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