<?php namespace GridView\Buttons;

/**
 * create a button with View as a label and linked with the given url
 */
class ViewButton extends Button {
	public $label = 'View';
	public $css = 'btn btn-info btn-xs';

	public function __construct($url, $config=array())
	{
		$this->url = $url;
		parent::__construct($config);
	}
}