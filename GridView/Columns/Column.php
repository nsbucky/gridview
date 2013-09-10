<?php namespace GridView\Columns;

/**
 * column class is used to eventually create a table cell in the parent table
 * if name is specified then that key is looked for in the data source, and used
 * to find the value, and to create the header. if value
 * is specified then just value is used for the table cell
 * 
 */
class Column implements ColumnInterface {
	
    /**
     * header value 
     * @var string
     */
	public $header;
    
    /**
     * the value to be used in the table cell. if no value is specified, then
     * the $name will be used to lookup a value in $dataSource
     * 
     * @var string
     */
    public $value;
    
    /**
     * this value will be used to lookup a value from $dataSource
     * @var string
     */
    public $name;
    
    /**
     * the html used as a filter for the table filter row
     * 
     * @var string
     */
    public $filter;
    
    /**
     * css classes to be used on the this column's row
     * 
     * @var string
     */
    public $rowCss;
    
    /**
     * the css classes to use on the table cell
     * 
     * @var string
     */
	public $cellCss = 'grid-view-column';
    
    /**
     * by default this column will be visible in the grid. if false
     * no rendering will be done for this column
     * 
     * @var boolean
     */
	public $visible  = true;
    
    /**
     * if this is set to true then render sort links for this column
     * 
     * @var boolean
     */
	public $sortable = false;
    
    /**
     * by default the $name attribute will be used when creating sort links, this value
     * if set will be used instead
     * 
     * @var string
     */
	public $sortableName;
    
    /**
     * string of javascript to be used for this column
     * 
     * @var string
     */
	public $javascript;
    
    /**
     * tokens created from the $dataSource in the format of {key}=>value
     * @var array
     */
	protected $tokens = array();
    
    /**
     * the table instance injected into this column
     * 
     * @var \GridView\Table
     */
	protected $table;
    
    /**
     * data passed from the table to this object to be used to find name/value etc
     * @var mixed
     */
	protected $data = array();

    /**
     * Constructor
     * @param array $config
     */
	public function __construct(array $config)
	{		
		foreach($config as $var=>$value) {
			$this->$var = $value;
		}	

		// make sure that you can get some sort of name.
        if(!$this->sortable) return;
        		
        if(!isset($this->sortableName) && isset($this->name)) {
            $this->sortableName = $this->name;
        }

        if(!$this->sortableName) $this->sortable = false;

	}

    /**
     * inject table instance
     * 
     * @param \GridView\Table $table
     * @return \GridView\Columns\Column
     */
	public function setTable(\GridView\Table $table)
	{
		$this->table = $table;
		return $this;
	}

    /**
     * Data passed from table to this column
     * 
     * @param type $data
     * @return \GridView\Columns\Column
     */
	public function setData($data)
	{
		$this->data = $data;	
		$this->tokenize($data);	
		return $this;
	}

    /**
     * String of html to be used as a filter for this columns header filter row
     * 
     * @return string
     */
    public function getFilter()
    {
        $value = null;
        
        if(isset($_GET[$this->name])) {
            $value = htmlentities($_GET[$this->name], ENT_QUOTES);            
        }
        
        if( isset($this->filter) && is_array($this->filter) ) {
            return sprintf(
                '<select name="%s" class="form-control">%s</select>',
                $this->name,
                $this->buildDropDownList($this->filter, $value)
            );
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
    
    /**
     * build a drop down list (select) from an array
     * 
     * @param array $options
     * @param string $selectedValue
     * @return string
     */
    protected function buildDropDownList(array $options, $selectedValue = null)
    {
        $optionsHtml = '';
        foreach($options as $key=>$value) {
            if(  is_array( $value ) ) {
                $optionsHtml .= sprintf(
                    '<optgroup label="%s">%s</optgroup>', 
                    $key,
                    $this->listOptions( $value, $selectedValue )
                );                
                continue;
            }
            $optionsHtml .= $this->listOptions(array($key=>$value), $selectedValue);            
        }
        
        return $optionsHtml;
    }
    
    /**
     * create options tags from array
     * 
     * @param array $options
     * @param string $selectedValue
     * @return string
     */
    protected function listOptions(array $options, $selectedValue = null)
    {
        $optionsHtml = '';
        
        foreach($options as $key=>$value) {            
            $selected = null;
            if($selectedValue == $key) {
                $selected = 'selected="selected"';
            }
            $optionsHtml .= sprintf(
                '<option value="%s" %s>%s</option>', 
                htmlspecialchars($key, ENT_QUOTES), 
                $selected, 
                htmlspecialchars($value, ENT_QUOTES)
            );            
        }
        
        return $optionsHtml;
    }

    /**
     * Get the header string for this column. If no header is specifically set
     * try to create a nice readable header based on $name
     * @return string
     */
	public function getHeader()
	{
		if( !isset($this->header) ) {            
			$h = str_replace('_', ' ', $this->name);
			$this->header = ucwords($h);
		}
		return $this->header;
	}

    /**
     * get the value to be used in the table cell. if the value set is callable
     * pass data to the callable function and get the value from it
     * 
     * @param mixed $index
     * @return string
     * @throws \Exception if name or value are not set for column
     */
	public function getValue($index)
	{				
		if( !isset($this->name) && !isset($this->value) ) {
			throw new \Exception('You must set a name or value for a column to render.');
		}

        if( is_callable($this->value) ) {
            $func = $this->value;
            return $func($this->data, $index);
        }
        
		if( isset($this->value) ) {
        	return $this->value;
		}

		if( is_array($this->data) ) {
            
            if( is_array($this->data[$this->name]) ) {
                return 'Array()';
            }
            
			return htmlspecialchars( (string) $this->data[$this->name], ENT_QUOTES );
		}

		if( is_object($this->data) ) {
            
            if( is_array($this->data->{$this->name}) ) {
                return 'Array()';
            }
            
			return htmlspecialchars( (string) $this->data->{$this->name}, ENT_QUOTES );
		}
	}

    /**
     * is column visible?
     * 
     * @return boolean
     */
	public function isVisible()
	{
		return (bool) $this->visible;
	}

    /**
     * create an array of tokens to be used in various column functions. if the
     * data pass is an object, it will first see if the object has a method called
     * isArray() to create an array from the object, otherwise it will cast the object
     * to an array and hope for the best
     * 
     * @param mixed $data
     * @return array
     */
	public function tokenize($data)
	{		
		if(is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

		foreach((array) $data as $key=>$value) {
            if( is_array($value) ) continue;
			$this->tokens['{'.$key.'}'] = (string) $value;
		}

		return $this->tokens;
	}

    /**
     * get create tokens
     * 
     * @return array
     */
	public function getTokens()
	{
		return $this->tokens;		
	}

    /**
     * get javascript set by column
     * 
     * @return string
     */
	public function getJavaScript()
	{
		return $this->javascript;
	}

    /**
     * get footer specified by this column. hopefully will be overriden by
     * extended classes
     * 
     * @return array
     */
	public function getFooter()
	{
		return array('value'=>null,'format'=>null);
	}

    /**
     * replace any tokens found in string with tokens
     * @param string $string
     * @return string
     */
	public function replaceTokens($string)
	{
		$tokens = $this->getTokens();
		if (strpos($string, '{') !== false) {			
			return str_replace(array_keys($tokens), array_values($tokens), (string) $string);
		}
		return $string;
	}
}