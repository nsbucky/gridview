# GridView
Inspired by Yii's CGridView, this class strives to be a simple way to generate a table from an array, ie; a database result set. It works off of an array of arrays, or an array of objects. You decide which array columns or object properties to display. It does not rewrite queries or handle paging, that is up to you.

## Features
- Configurable headers
- Generates sort urls
- Uses Bootstrap table css by default
- Provided column types enable quicker development time
- Table implements ArrayAccess for easier building of tables.

## Column Types
GridView offers a few different column types for calculations or displaying certain data types.

- Column: default column type, supports visiblity and custom headers and filters
- CalcColumn: Pass in a closure to perform a calculation 
- CheckBoxColumn: Generate a checkbox in a table cell
- DateTimeColumn: Use php's DateTime object to format date strings
- LinkColumn: generate anchor tags
- TotalColumn: sum columns and puts a total in the table footer
- ButtonColumn: useful for actions like view/edit/delete or whatever you would like. Comes with sample buttons

### Required setup

In the `require` key of `composer.json` file add the following

    "nsbucky/gridview": "dev-master"

## Quick Example
	$dataSource = array();
	for($i=0; $i<10; $i++) {
		$dataSource[] = array(
		            	'uniqid'=>uniqid(), 
		            	'loop_iterator'=>$i.' times',
		            	'date'=>date('Y-m-d'),
		            	'total'=>rand(1,25)
		           	);		        
	}

	$table = new GridView\Table($dataSource);
	echo $table; // renders table based on results

	$table = new GridView\Table($dataSource);
	$table->addViewButton('view/{uniqid}')
        ->addEditButton('edit/me')
        ->addDeleteButton('delete/it');
    echo $table; // will render table based on results with some buttons in last column

    // in a list view
    $table = new GridView\TableList($dataSource[0]);
    echo $table; // will render a 2 column table with the column headers on left

## Longer Example
	$dataSource = array();
	for($i=0; $i<10; $i++) {
		$dataSource[] = array(
		            	'uniqid'=>uniqid(), 
		            	'loop_iterator'=>$i.' times',
		            	'date'=>date('Y-m-d'),
		            	'total'=>rand(1,25)
		           	);		        
	}

	$table = new GridView\Table($dataSource);
	$table->addColumn(
		array('name'=>'uniqid') // defaults to GridView\Columns\Column
	)
	->addColumn(
		new GridView\Columns\LinkColumn(array(
			'url'=>'/link/to/date/{date}',
			'label'=>'Date Link'
		))
	)
	->addColumn(
		new GridView\Columns\Column(array(
			'name'=>'total',
			'value'=>function($data, $index) {
				return $data['total']. ' dinosaurs were eaten today'
			}
		))
	)
	->addColumn(
		new GridView\Columns\DateTimeColumn(array(
			'name'=>'date',
			'visible'=> (date('j') % 2)
		)) 
	);


	// or via array access
	$table[] = array('name'=>'loop_iterator');

	$table[] = new GridView\Columns\ButtonColumn(array(
            'buttons'=>array(
                new GridView\Buttons\ViewButton('/view'),
                new GridView\Buttons\EditButton('/edit'),
                new GridView\Buttons\DeleteButton('/delete'),
            )
        ));

	echo (string) $table; // renders table