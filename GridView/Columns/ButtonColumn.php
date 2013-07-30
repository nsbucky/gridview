<?php namespace GridView\Columns;

class ButtonColumn extends Column {
	public $buttons = array();
	public $name = 'Actions';

	public function getValue($data, $index)
	{
		$s = '';
		$tokens = $this->tokenize($data);
		foreach($this->buttons as $button) {			
			$button->setTokens($tokens);
			$s .= $button->render().' '.PHP_EOL;
		}

		return $s;
	}

}