<?php namespace GridView\Buttons;

class EditButton extends Button {
	public $label = 'Edit';
	public $css = 'btn btn-success btn-small';

	public function __construct($url, $config=array())
	{
		$this->url = $url;
		parent::__construct($config);
	}
}