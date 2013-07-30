<?php namespace GridView\Buttons;

class Button implements ButtonInterface {

	public $css = 'btn btn-small';
	public $url;
	public $label;
	public $confirm = false;
	public $tokens = array();

	public function __construct(array $config)
	{		
		foreach($config as $var=>$value) {
			$this->$var = $value;
		}
	}

	public function setTokens(array $tokens)
	{
		$this->tokens = $tokens;
	}

	public function render()
	{
		$url = $this->getUrl($this->tokens);
		$label = $this->getLabel($this->tokens);
		$onclick = null;
		if($this->confirm) {
			$onclick = 'onclick="return confirm(\'Are you sure you want to do this?\')"';
		}		
		return sprintf('<a href="%s" class="%s" %s>%s</a>', $url, $this->css, $onclick, $label);
	}
	
	public function getUrl($data)
	{
		if(is_callable($this->url)) {
			$func = $this->url;
			return $func($data);
		}

		if (strpos($this->url, '{') !== false) {			
			return str_replace(array_keys($this->tokens), array_values($this->tokens), $this->url);
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
			return str_replace(array_keys($this->tokens), array_values($this->tokens), $this->label);
		}

		return $this->label;
	}

	public function __toString()
	{
		return $this->render();
	}
}