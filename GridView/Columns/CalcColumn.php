<?php

namespace GridView\Columns;

class CalcColumn extends Column
{
	public $calculation;

	public function getValue($data, $index)
	{
		if (!is_callable($this->calculation)) {
			return 0;
		}

		$func = $this->calculation;
		return $func($data);
	}
}