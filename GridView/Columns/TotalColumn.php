<?php namespace GridView\Columns;

class TotalColumn extends Column
{	
	public $filter = false;
	public $format;

	public function getValue($data, $index)
	{
		$value = parent::getValue($data, $index);
		$total = $this->table->getFooterValue($this->name);
		$this->table->setFooterValue($this->name, $total + $value);
		return $value;		
	}

	public function getFooter()
	{
        if(!is_callable($this->format)) {
            $this->format = function($v) {
                return sprintf('%d', $v);
            };
        }
		return array('value'=>0, 'format'=>$this->format);
	}
}