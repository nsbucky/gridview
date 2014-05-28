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

        if( empty($value) ) {
            return null;
        }

        try {
            $date = new \DateTime($value);
            return $date->format($this->format);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getJavaScript()
    {
        if( $this->table->useModalFilters ) {
            $this->table->javascript .= <<<__JS__
<script type="text/javascript">

   \$('.datetimeColumn').daterangepicker({
        format: "YYYY-MM-DD",
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
            'Last 7 Days': [moment().subtract('days', 6), moment()],
            'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
            'Month To Date': [moment().startOf('month'), moment()],
            'Year To Date': [moment().startOf('year'), moment()]
         }
    });

</script>
__JS__;

        } else {

            $this->table->javascript .= <<<__JS__
<script type="text/javascript">

   \$('.datetimeColumn').daterangepicker({
        format: "YYYY-MM-DD",
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
            'Last 7 Days': [moment().subtract('days', 6), moment()],
            'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
            'Month To Date': [moment().startOf('month'), moment()],
            'Year To Date': [moment().startOf('year'), moment()]
         }
    }, function(start, end){
        var values = $('#{$this->table->id} .grid-view-filters :input').serialize();
		window.location = window.location.pathname+'?'+values+'&{$this->name}='+start.format('YYYY-MM-DD')+' - '+end.format('YYYY-MM-DD');
    });

</script>
__JS__;
        }

    }

    public function getFilter()
    {
        $dateValue = '';
        if( isset( $_GET[$this->name]) ) {
            $dateValue = htmlentities( $_GET[$this->name] );
        }

        if( $this->table->useModalFilters ) {
            return sprintf(
                '<div class="grid-view-filter-container">
                <input type="text" name="%s" class="grid-view-filter input-small form-control datetimeColumn" value="%s">
                </div>',
                $this->name,
                $dateValue
            );
        }

        return '<div class="datetimeColumn" title="Showing: '.$dateValue.'"><span class="glyphicon glyphicon-calendar"></span></div>';
    }
}