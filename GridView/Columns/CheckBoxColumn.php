<?php namespace GridView\Columns;

class CheckBoxColumn extends Column
{
	public $checked = false;	
	public $cellCss = 'grid-view-checkbox-column';

	public function getFilter()
	{
		return '<input type="checkbox" name="select-all" class="grid-view-select-all">';
	}

	public function getValue($index)
	{
		$value = parent::getValue($index);
		$checked = $this->isChecked($this->data) ? 'checked="checked"' : null;
		return sprintf('<input type="checkbox" name="%s[]" class="grid-view-checkbox" value="%s" %s>', 
			           $this->name,
			           $value,
			           $checked);
	}

	public function isChecked($data)
	{
		if(is_callable($this->checked)) {
			$func = $this->checked;
			return (bool) $func($data);
		} 

		return (bool) $this->checked;
	}

	public function getJavascript()
	{
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