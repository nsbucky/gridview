<?php namespace GridView\Buttons;

class DeleteButton extends Button {
	public $label = 'Delete';
	public $confirm = true;
	public $css = 'btn btn-danger btn-small';

	public function __construct($url, $config=array())
	{
		$this->url = $url;
		parent::__construct($config);
	}

	public function render()
	{
		$url = $this->getUrl($this->tokens);
		$label = $this->getLabel($this->tokens);
		$onclick = null;
		if($this->confirm) {
			$onclick = 'onclick="return confirm(\'Are you sure you want to do this?\')"';
		}		
		return sprintf(
				'<form action="%s" method="post" class="form-inline"><input type="submit" name="grid-view-submit" value="%s" class="%s" %s></form>', 
				$url, 
				$label,
				$this->css, 
				$onclick				
		);
	}
}