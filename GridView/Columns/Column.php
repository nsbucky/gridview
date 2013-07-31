<?php namespace GridView\Columns;

class Column implements ColumnInterface {
	// none of these values are escaped.
	public $header, $value, $name, $filter, $rowCss;
	public $cellCss = 'grid-view-column';
	public $visible  = true;
	public $sortable = false;
	public $sortableName;
	public $javascript;
	protected $tokens = array();
	protected $table;
	protected $data = array();

	public function __construct(array $config)
	{		
		foreach($config as $var=>$value) {
			$this->$var = $value;
		}	

		// make sure that you can get some sort of name.
		if($this->sortable) {
			if(!isset($this->sortableName) && isset($this->name)) {
				$this->sortableName = $this->name;
			}

			if(!$this->sortableName) $this->sortable = false;
		}	
	}

	public function setTable(\GridView\Table $table)
	{
		$this->table = $table;
		return $this;
	}

	public function setData($data)
	{
		$this->data = $data;		
		return $this;
	}

	public function getFilter()
	{
		$value = null;
		
		if(isset($_GET[$this->name])) {
			$value = htmlentities($this->name, ENT_QUOTES);
		}

		if (!isset($this->filter)) {
			return sprintf(
				'<div class="grid-view-filter-container">
				<input type="text" name="%s" style="width:100%%" class="grid-view-filter input-small form-control" value="%s">
				</div>',
				$this->name,
				$value
			);
		}

		return $this->filter;
	}

	public function getHeader()
	{
		if(!isset($this->header)) {
			$h = str_replace('_', ' ', $this->name);
			$this->header = ucwords($h);
		}
		return $this->header;
	}

	public function getValue($index)
	{				
		if(!isset($this->name) && !isset($this->value)) {
			throw new \Exception('You must set a name or value for a column to render.');
		}

		if(isset($this->value)) {
			if(is_callable($this->value)) {
				$func = $this->value;
				return $func($this->data, $index);
			}

			return $this->value;
		}

		if(is_array($this->data)) {
			return htmlspecialchars($this->data[$this->name], ENT_QUOTES);
		}

		if(is_object($this->data)) {
			return htmlspecialchars($this->data->{$this->name}, ENT_QUOTES);
		}
	}

	public function isVisible()
	{
		return (bool) $this->visible;
	}

	public function tokenize($data)
	{		
		if(is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

		foreach((array) $data as $key=>$value) {
			$this->tokens['{'.$key.'}'] = $value;
		}

		return $this->tokens;
	}

	public function getTokens()
	{
		if(count($this->tokens)) return $this->tokens;
		return $this->tokenize($this->data);		
	}

	public function getJavaScript()
	{
		return $this->javascript;
	}

	public function getFooter()
	{
		return array('value'=>null,'format'=>null);
	}

	public function replaceTokens($string)
	{
		$tokens = $this->getTokens();
		if (strpos($string, '{') !== false) {			
			return str_replace(array_keys($tokens), array_values($tokens), $string);
		}
		return $string;
	}
}