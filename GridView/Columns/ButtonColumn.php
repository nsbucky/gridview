<?php namespace GridView\Columns;

class ButtonColumn extends Column {
	public $buttons = array();
	public $name = 'Actions';
	public $filter = false;
	public $cellCss = 'grid-view-button-column';

	public function getValue($index)
	{
		$s = '';		
		foreach($this->buttons as $button) {			
			$button->setTokens($this->getTokens());
			$s .= $button->render().' '.PHP_EOL;
		}

		return $s;
	}

}