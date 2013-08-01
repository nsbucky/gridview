<?php namespace GridView\Columns;

/**
 * this class will create 1 table cell that is filled with the \Buttons\Button 
 * that you pass to it.
 * 
 */
class ButtonColumn extends Column {
	public $buttons = array();
	public $name = 'Actions';
	public $filter = false;
	public $cellCss = 'grid-view-button-column';

	public function getValue($index)
	{
		$s = '';		
		$tokens = $this->getTokens();
		foreach($this->buttons as $button) {			
			$button->setTokens($tokens);
			$s .= $button->render().' '.PHP_EOL;
		}

		return $s;
	}

}