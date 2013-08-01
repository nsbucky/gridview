<?php namespace GridView\Columns;

/**
 * This column can be used to perform user specified calculations on any data
 * that is found in $dataSource. It will return 0 if no calculation can be performed
 * like if you forgot to specify a calculation callable
 */
class CalcColumn extends Column {
	public $calculation;
	public $cellCss = 'grid-view-calc-column';

	public function getValue($index)
	{
		if (!is_callable($this->calculation)) {
			return 0;
		}

		$func = $this->calculation;
		return $func($this->data);
	}
}