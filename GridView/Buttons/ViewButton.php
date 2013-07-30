<?php namespace GridView\Buttons;

class ViewButton extends Button {
	public $label = 'View';
	public $css = 'btn btn-info btn-small';

	public function __construct($url, $config=array())
	{
		$this->url = $url;
		parent::__construct($config);
	}
}