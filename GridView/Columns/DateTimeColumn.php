<?php namespace GridView\Columns;

class DateTimeColumn extends Column
{
	public $format = 'Y-m-d H:i:s';	

	public function getValue($data, $index)
	{
		$value = parent::getValue($data, $index);
		try {
			$date = new \DateTime($value);
			return $date->format($this->format);
		} catch (\Exception $e) {
			return $e->getMessage();
		}		
	}
}