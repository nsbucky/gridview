<?php namespace GridView\Columns;

class DateTimeColumn extends Column
{
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