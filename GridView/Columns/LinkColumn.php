<?php namespace GridView\Columns;

/**
 * this class should be used to render anchor tags in a column cell. you can use
 * token replacements in the url and header to generate links based off of the data
 * set in the table
 * 
 */
class LinkColumn extends Column {
	public $url, $label;
	public $linkCss = '';
	public $cellCss = 'grid-view-link-column';
	
    /**
     * Constructor.
     * 
     * @param array $config
     */
	public function __construct(array $config)
	{
		//$this->sortable = false;
		
		//unset($config['sortable'], $config['sortableName']);

		parent::__construct($config);
	}

    /**
     * Create the anchor tag for the table cell
     * 
     * @param mixed $index
     * @return string
     */
	public function getValue($index)
	{
		return sprintf('<a href="%s" class="%s">%s</a>', $this->getUrl(), $this->linkCss, $this->getLabel());	
	}

    /**
     * get the url to be used in the anchor tag. if the url is callable, then 
     * pass data to the url function, and then on to the token replacement
     * @return string
     */
	public function getUrl()
	{
        $url = $this->url;
		if(is_callable($this->url)) {
			$func = $this->url;
			$url = $func($this->data);
		}

		return $this->replaceTokens($url);
	}

    /**
     * get the label to be used in the anchor tag. if the label is callable, then 
     * pass data to the label function, and then on to the token replacement
     * @return string
     */
	public function getLabel()
	{
        $label = $this->label;        
		if(is_callable($this->label)) {
			$func = $this->label;
			$label = $func($this->data);
		}

		return $this->replaceTokens($label);		
	}

    /**
     * get header string, if any
     * 
     * @return mixed
     */
	public function getHeader()
	{
		if (isset($this->header)) {
			return $this->header;
		}

		return parent::getHeader();
	}

}