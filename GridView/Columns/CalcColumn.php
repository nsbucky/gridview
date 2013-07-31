<?php

namespace GridView\Columns;

class CalcColumn extends Column
{
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