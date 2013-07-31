<?php namespace GridView\Columns;

class LinkColumn extends Column
{
	public $url, $label;
	public $linkCss = 'btn';
	public $cellCss = 'grid-view-link-column';
	
	public function __construct(array $config)
	{
		$this->sortable = false;
		
		unset($config['sortable'], $config['sortableName']);

		parent::__construct($config);
	}

	public function getValue($index)
	{
		return sprintf('<a href="%s" class="%s">%s</a>', $this->getUrl(), $this->linkCss, $this->getLabel());	
	}

	public function getUrl()
	{
		if(is_callable($this->url)) {
			$func = $this->url;
			return $func($this->data);
		}

		return $this->replaceTokens($this->url);
	}

	public function getLabel()
	{
		if(is_callable($this->label)) {
			$func = $this->label;
			return $func($this->data);
		}

		return $this->replaceTokens($this->label);		
	}

	public function getHeader()
	{
		if (isset($this->header)) {
			return $this->header;
		}

		return null;
	}

	public function getFilter()
	{
		return null;
	}
}