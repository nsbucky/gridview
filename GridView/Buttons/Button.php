<?php namespace GridView\Buttons;

class Button implements ButtonInterface {

    /**
     * html tag css
     * 
     * @var string
     */
	public $css = 'btn btn-xs btn-default';
    
    /**
     * url to be used for link
     * 
     * @var string
     */
	public $url;
    
    /**
     * label
     * 
     * @var string
     */
	public $label;
    
    /**
     * does onclick event fire a javascript confirm dialog
     * 
     * @var boolean
     */
	public $confirm = false;
    
    /**
     * tokenized {key}=>value
     * 
     * @var array
     */
	public $tokens = array();

    /**
     * Constructor
     * 
     * @param array $config
     */
	public function __construct(array $config)
	{		
		foreach($config as $var=>$value) {
			$this->$var = $value;
		}
	}

    /**
     * set tokens to use for this button
     * 
     * @param array $tokens
     */
	public function setTokens(array $tokens)
	{
		$this->tokens = $tokens;
	}

    /**
     * create html to be used as a button in the column and optionally create
     * a javascript confirm onclick event
     * 
     * @return string
     */
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
	
    /**
     * get the url to be used for this button, replacing any tokens found in the
     * string
     * 
     * @param array $data
     * @return string
     */
	public function getUrl($data)
	{
		if(is_callable($this->url)) {
			$func = $this->url;
			return $func($data);
		}

		return $this->replaceTokens($this->url);
	}

    /**
     * get the label to be used for this button, replacing any tokens found in the
     * string
     * 
     * @param array $data
     * @return string
     */
	public function getLabel($data)
	{
		if(is_callable($this->label)) {
			$func = $this->label;
			return $func($data);
		}

		return $this->replaceTokens($this->label);
	}

    /**
     * call render when this class is cast as a string
     * 
     * @return string
     */
	public function __toString()
	{
		return $this->render();
	}

    /**
     * replace any tokens found in string with stuff from the $dataSource
     * 
     * @param string $string
     * @return string
     */
	public function replaceTokens($string)
	{		
		if (strpos($string, '{') !== false) {			
			return str_replace(array_keys($this->tokens), array_values($this->tokens), $string);
		}
		return $string;
	}
}