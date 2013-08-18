<?php namespace GridView\Columns;

/**
 * This column can be used to perform user specified calculations on any data
 * that is found in $dataSource. It will return 0 if no calculation can be performed
 * like if you forgot to specify a calculation callable
 */
class BooleanColumn extends Column {
    
	public $trueLabel = 'Yes';	
    public $falseLabel = 'No';
    public $trueValues = array('yes','true','1','on');
    public $falseValues = array('no','false','0','off');

	public function getValue($index)
	{
		$value = parent::getValue($index);
        
        // check and see if value is one of these:
        // 1, true, yes
        $labelHtml = '<span class="label %s">%s</span>';
        $labelCss  = 'label-default';
        
        if( in_array(strtolower( (string) $value ), $this->trueValues, true) ) {
            $value = $this->trueLabel;
            $labelCss = 'label-success';
        }
        
        if( in_array(strtolower( (string) $value ), $this->falseValues, true) ) {
            $value = $this->falseLabel;
            $labelCss = 'label-danger';
        }
        
        return sprintf($labelHtml, $labelCss, $value);
	}
}