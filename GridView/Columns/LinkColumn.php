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

	public function getValue($data, $index)
	{
		return sprintf('<a href="%s" class="%s">%s</a>', $this->getUrl($data), $this->linkCss, $this->getLabel($data));	
	}

	public function getUrl($data)
	{
		if(is_callable($this->url)) {
			$func = $this->url;
			return $func($data);
		}

		if (strpos($this->url, '{') !== false) {
			$tokens = $this->tokenize($data);
			return str_replace(array_keys($tokens), array_values($tokens), $this->url);
		}

		return $this->url;
	}

	public function getLabel($data)
	{
		if(is_callable($this->label)) {
			$func = $this->label;
			return $func($data);
		}

		if (strpos($this->label, '{') !== false) {
			$tokens = $this->tokenize();
			return str_replace(array_keys($tokens), array_values($tokens), $this->label);
		}

		return $this->label;
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