<?php namespace GridView\Buttons;

/**
 * create a button that will have Edit as a label and will have a link to the 
 * given url
 * 
 */
class EditButton extends Button {
	public $label = 'Edit';
	public $css = 'btn btn-success btn-small';

	public function __construct($url, $config=array())
	{
		$this->url = $url;
		parent::__construct($config);
	}
}