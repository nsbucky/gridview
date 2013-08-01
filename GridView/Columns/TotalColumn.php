<?php namespace GridView\Columns;

/**
 * this class is used to sum up amounts in this column, then put a total in the
 * table footer
 */
class TotalColumn extends Column {	
	public $filter = false;
	public $format;
	public $cellCss = 'grid-view-total-column';

	public function getValue($index)
	{
		$value = parent::getValue($index);
		$total = $this->table->getFooterValue($this->name);
		$this->table->setFooterValue($this->name, $total + $value);
		return $value;		
	}

	public function getFooter()
	{
        // if format is not a callable function, then lets make one that just 
        // returns the total as a number
        if( !is_callable($this->format) ) {
            $this->format = function($v) {
                return sprintf('%d', $v);
            };
        }
		return array('value'=>0, 'format'=>$this->format);
	}
}