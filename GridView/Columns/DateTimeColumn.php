<?php namespace GridView\Columns;

/**
 * this class can be used to display a string of date/time in a column
 * php's \DateTime class will be used to interpret the columns value and
 * create a \DateTime object. Output format can be set.
 * http://www.dangrossman.info/2012/08/20/a-date-range-picker-for-twitter-bootstrap/
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
    
    public function getJavaScript()
    {
        $js = <<<__JS__
<script type="text/javascript">      
   
   \$('.datetimeColumn').daterangepicker({
        format: "YYYY-MM-DD",
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
            'Last 7 Days': [moment().subtract('days', 6), moment()],
            'Last 30 Days': [moment().subtract('days', 29), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
         }
    }, function(start, end){
        var values = $('#{$this->table->id} .grid-view-filters :input').serialize();        
		window.location = window.location.pathname+'?'+values+'&{$this->name}='+start.format('YYYY-MM-DD')+' - '+end.format('YYYY-MM-DD');
    });
   
</script>       
__JS__;
        $this->javascript = $js;
        return $js;
    }
    
    public function getFilter()
    {
        return '<div class="datetimeColumn"><span class="glyphicon glyphicon-calendar"></span></div>';
    }
}