<?php namespace GridView\Columns;

//http://vitalets.github.io/x-editable/docs.html

class EditableColumn extends Column {
    
    public $fieldType  = 'text';
    public $fieldOptions = array();
    public $editUrl    = '';
    public $primaryKey = 'id';
    public $prompt     = 'Enter Value';
    public $fieldName; // will default to $this->name;
    public $initialValue; // for selects
    public $editableId = '.editable';
    public $enableEditing = true;

    /**
     * @param mixed $index
     * @return string
     */
    public function getValue( $index )
    {
        $value = parent::getValue( $index );
        
        if( ! $this->enableEditing ) {
            return $value;
        }
        
        //assume server response: 200 Ok {status: 'error', msg: 'field cannot be empty!'}
        $this->javascript =<<<__JS__
<script type="text/javascript">
    jQuery(function(){jQuery($this->editableId).editable({
            success: function(response, newValue){
                if(response.status == 'error') return response.msg;
            }
        });
    });
</script>
__JS__;
        
        if( strlen($value) > 50 ) {
            $this->fieldType = 'textarea';
        }
        
        return sprintf('<a href="#" class="editable" data-type="%s" data-pk=\'%s\' data-url="%s" data-title="%s" %s>%s</a>',
            $this->fieldType,
            $this->getPrimaryKeyValue(),
            $this->editUrl,
            $this->prompt,
            $this->getFieldOptions(),
            $value
        );
    }

    /**
     * @return string
     */
    public function getFieldOptions()
    {
        if( count($this->fieldOptions) > 0 ) {
            $s = '';
            foreach( $this->fieldOptions as $optionName=>$optionValue ) {
                $s .= " data-$optionName='$optionValue' ";
            }
            
            return $s;
        }
        
        return '';
    }

    /**
     * @return string
     */
    public function getPrimaryKeyValue()
    {               
        if( is_callable( $this->primaryKey ) ) {
            $func = $this->primaryKey;
            return $func($this->data);
        }
        
        // try to grab it auto from dataset
        $pk = $this->primaryKey;
        
        if( is_array($this->data) ) {
			return (string) $this->data[$pk];
		}

		if( is_object($this->data) ) {            
			return (string) $this->data->$pk;
		}
    }
    
}