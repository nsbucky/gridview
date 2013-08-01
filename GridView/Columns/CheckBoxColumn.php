<?php namespace GridView\Columns;

/**
 * this column will create a checkbox in the table cell, and a checkbox for the
 * header table cell. If the tables $useJqueryJavascripts is set to true, it will
 * also create javascript to select/unselect all checkboxes when the header checkbox
 * is clicked.
 * 
 */
class CheckBoxColumn extends Column {
	public $checked = false;	
	public $cellCss = 'grid-view-checkbox-column';

    /**
     * create a checkbox instead of an input[text] field for the header
     * 
     * @return string
     */
	public function getFilter()
	{
		return '<input type="checkbox" name="select-all" class="grid-view-select-all">';
	}

    /**
     * Render a checkbox while checking if the checkbox should be checked by default
     * 
     * @param type $index
     * @return string
     */
	public function getValue($index)
	{
		$value = parent::getValue($index);
		$checked = $this->isChecked($this->data) ? 'checked="checked"' : null;
		return sprintf('<input type="checkbox" name="%s[]" class="grid-view-checkbox" value="%s" %s>', 
			           $this->name,
			           $value,
			           $checked);
	}

    /**
     * use the passed data to determine if the checkbox from getValue() should be
     * checked or not
     * 
     * @param mixed $data
     * @return boolean
     */
	public function isChecked($data)
	{
		if(is_callable($this->checked)) {
			$func = $this->checked;
			return (bool) $func($data);
		} 

		return (bool) $this->checked;
	}

    /**
     * if $useJqueryJavascripts is true in the parent table, then create some
     * javascript to handle the select all checkbox in the header
     * 
     * @return string
     */
	public function getJavascript()
	{
        if( !$this->table->useJqueryJavascripts ) return '';
		ob_start()
		?>
		<script>
		jQuery(function(){
			$('.grid-view-select-all').click(function(){
				if($(this).checked) {
					$('.grid-view-checkbox').attr('checked', true);
				} else {
					$('.grid-view-checkbox').attr('checked', false);
				}
			});
		});
		</script>
		<?php
		return ob_get_clean();
	}
}