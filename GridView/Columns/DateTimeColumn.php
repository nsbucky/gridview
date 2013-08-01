<?php namespace GridView\Columns;

/**
 * this class can be used to display a string of date/time in a column
 * php's \DateTime class will be used to interpret the columns value and
 * create a \DateTime object. Output format can be set.
 * 
 */
class DateTimeColumn extends Column {
	public $format = 'Y-m-d H:i:s';	
	public $cellCss = 'grid-view-datetime-column';

	public function getValue($index)
	{
		$value = parent::getValue($index);
		try {
			$date = new \DateTime($value);
			return $date->format($this->format);
		} catch (\Exception $e) {
			return $e->getMessage();
		}		
	}
}