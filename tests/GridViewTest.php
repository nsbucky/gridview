<?php

require __DIR__.'/../GridView/Table.php';
require __DIR__.'/../GridView/TableList.php';
require __DIR__.'/../GridView/Columns/ColumnInterface.php';
require __DIR__.'/../GridView/Buttons/ButtonInterface.php';
require __DIR__.'/../GridView/Columns/Column.php';
require __DIR__.'/../GridView/Columns/CalcColumn.php';
require __DIR__.'/../GridView/Columns/CheckBoxColumn.php';
require __DIR__.'/../GridView/Columns/DateTimeColumn.php';
require __DIR__.'/../GridView/Columns/TotalColumn.php';
require __DIR__.'/../GridView/Columns/LinkColumn.php';
require __DIR__.'/../GridView/Columns/ButtonColumn.php';
require __DIR__.'/../GridView/Buttons/Button.php';
require __DIR__.'/../GridView/Buttons/ViewButton.php';
require __DIR__.'/../GridView/Buttons/EditButton.php';
require __DIR__.'/../GridView/Buttons/DeleteButton.php';

class GridViewTest extends PHPUnit_Framework_TestCase {

	public $dataSourceArray = array();
	public $dataSourceObject;

	public function __construct()
	{
		for($i=1; $i<=10; $i++) {
			$this->dataSourceArray[] = array('uniqid'=>uniqid(), 'loop_iterator'=>$i.' times','date'=>date('Y-m-d'),'total'=>rand(1,25));	
		}

		for($i=1; $i<=10; $i++) {
			$this->dataSourceObject[] = (object) array('uniqid'=>uniqid(), 'loop_iterator'=>$i.' times','date'=>date('Y-m-d'),'total'=>rand(1,25));	
		}
		return parent::__construct();
	}
         
    public function testSetConfigOptions()
    {
        $dataSource = array(
            array('uniqid'=>'123', 'loop_iterator'=>'4 times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        
        $options = array('sortDirection'=>'DESC');
        $table = new GridView\Table($dataSource, $options);
        
        $this->assertEquals('DESC', $table->sortDirection);        
    }

	public function testCreateTable()
	{
        $table = new GridView\Table($this->dataSourceArray);
        $string = $table->render();       
        $this->assertTag(
            array(
                'tag'=>'table',                       
				'attributes'=>array(
					'class'=>'table table-bordered table-striped',					
                ),
                'child'=>array(
                    'tag'=>'thead',                    
                ),
                'child'=>array(
                    'tag'=>'tbody',                    
                ),
            ),            
            $string
        );
	}
    
    public function testMagicMethod()
    {
        $i = 0;
        $u = uniqid();
        $dataSource = array(
            array('uniqid'=>$u, 'loop_iterator'=>$i.' times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        $table = new GridView\Table($dataSource);
        $table->addColumn(new GridView\Columns\Column(array(
            'name'=>'uniqid',
            'sortable'=>true
        )) );
        $table->addLinkColumn(array('name'=>'balls', 'url'=>'nuts/{uniqid}', 'label'=>'my link'));
        $string = $table->render();                
        
        $this->assertTag(
            array(
                'tag'=>'td',
                'child'=>array(
                    'tag'=>'a',
                    'attributes'=>array(
                        'href'=>'nuts/'.$u,
                    ),
                    'content'=>'my link'
                )
            ),            
            $string
        );
    }
    
    public function testAddColumn()
	{       
        $i = 5;
        $dataSource = array(
            array('uniqid'=>uniqid(), 'loop_iterator'=>$i.' times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        $table = new GridView\Table($dataSource);
        $table->addColumn(new GridView\Columns\Column(array(
            'name'=>'uniqid',
            'sortable'=>true
        )) );
        $string = $table->render();    
        
        $this->assertTag(
            array(
                'tag'=>'table', 
                'id'=>'GridViewTable',
				'attributes'=>array(
					'class'=>'table table-bordered table-striped',					
                ),
                'child'=>array(
                    'tag'=>'thead',   
                    'descendant'=>array(
                        'tag'=>'tr',
                        'attributes'=>array(
                            'class'=>'grid-view-headers'
                        ),
                        'descendant'=>array(
                            'tag'=>'th',
                            'child'=>array(
                                'tag'=>'a',
                                'attributes'=>array(
                                    'href'=>'?sort=uniqid&sort_dir=ASC',
                                    'class'=>'sort-link'
                                ),
                                'content'=>'Uniqid'
                            )
                        )
                    )
                ),
                'child'=>array(
                    'tag'=>'tbody',
                    'descendant'=>array(
                        'tag'=>'tr',
                        'descendant'=>array(
                            'tag'=>'td',
                            'content'=>$dataSource[0]['uniqid']
                        )
                    )                    
                )
            ),            
            $string
        );
	}

    public function testAddColumnFromObject()
    {       
        $i = 5;
        $dataSource = array(
            (object) array('uniqid'=>uniqid(), 'loop_iterator'=>$i.' times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        $table = new GridView\Table($dataSource);
        $table->addColumn(new GridView\Columns\Column(array(
            'name'=>'uniqid',
            'sortable'=>true
        )) );
        $string = $table->render();    
        #echo $string;
        $this->assertTag(
            array(
                'tag'=>'table',                       
                'attributes'=>array(
                    'class'=>'table table-bordered table-striped',                  
                ),
                'child'=>array(
                    'tag'=>'thead',   
                    'descendant'=>array(
                        'tag'=>'tr',
                        'attributes'=>array(
                            'class'=>'grid-view-headers'
                        ),
                        'descendant'=>array(
                            'tag'=>'th',
                            'child'=>array(
                                'tag'=>'a',
                                'attributes'=>array(
                                    'href'=>'?sort=uniqid&sort_dir=ASC',
                                    'class'=>'sort-link'
                                ),
                                'content'=>'Uniqid'
                            )
                        )
                    )
                ),
                'child'=>array(
                    'tag'=>'tbody',
                    'descendant'=>array(
                        'tag'=>'tr',
                        'descendant'=>array(
                            'tag'=>'td',
                            'content'=>$dataSource[0]->uniqid
                        )
                    )                    
                )
            ),            
            $string
        );
    }

    public function testAddColumnAsArray()
    {       
        $i = 5;
        $dataSource = array(
            array('uniqid'=>uniqid(), 'loop_iterator'=>$i.' times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        $table = new GridView\Table($dataSource);
        $table[] = new GridView\Columns\Column(array(
            'name'=>'uniqid',
            'sortable'=>true
        ));
        $string = $table->render();    
        #echo $string;
        $this->assertTag(
            array(
                'tag'=>'table',                       
                'attributes'=>array(
                    'class'=>'table table-bordered table-striped',                  
                ),
                'child'=>array(
                    'tag'=>'thead',   
                    'descendant'=>array(
                        'tag'=>'tr',
                        'attributes'=>array(
                            'class'=>'grid-view-headers'
                        ),
                        'descendant'=>array(
                            'tag'=>'th',
                            'child'=>array(
                                'tag'=>'a',
                                'attributes'=>array(
                                    'href'=>'?sort=uniqid&sort_dir=ASC',
                                    'class'=>'sort-link'
                                ),
                                'content'=>'Uniqid'
                            )
                        )
                    )
                ),
                'child'=>array(
                    'tag'=>'tbody',
                    'descendant'=>array(
                        'tag'=>'tr',
                        'descendant'=>array(
                            'tag'=>'td',
                            'content'=>$dataSource[0]['uniqid']
                        )
                    )                    
                )
            ),            
            $string
        );
    }
    
    public function testRenderHeader()
    {
        $i = 3;
        $dataSource = array(
            array('uniqid'=>uniqid(), 'loop_iterator'=>$i.' times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        $table = new GridView\Table($dataSource);
        $table->addColumn(new GridView\Columns\Column(array(
            'name'=>'uniqid',
            'sortable'=>true
        )) );
        $string = $table->render();    
        #echo $string;
        $this->assertTag(
            array(
                'tag'=>'table',                       
				'attributes'=>array(
					'class'=>'table table-bordered table-striped',					
                ),
                'child'=>array(
                    'tag'=>'thead',   
                    'descendant'=>array(
                        'tag'=>'tr',
                        'attributes'=>array(
                            'class'=>'grid-view-headers'
                        ),
                        'descendant'=>array(
                            'tag'=>'th',
                            'child'=>array(
                                'tag'=>'a',
                                'attributes'=>array(
                                    'href'=>'?sort=uniqid&sort_dir=ASC',
                                    'class'=>'sort-link'
                                ),
                                'content'=>'Uniqid'
                            )
                        )
                    )
                ),
            ),            
            $string
        );
    }
    
    public function testTotalColumn()
    {
        $table = new GridView\Table($this->dataSourceArray);
        $table->addColumn(new GridView\Columns\TotalColumn(array(
            'name'=>'total',
        )) );
        
        $string = $table->render();    
        #echo $string;
        $this->assertTag(
            array(
                'tag'=>'table',                       
				'attributes'=>array(
					'class'=>'table table-bordered table-striped',					
                ),
                'child'=>array(
                    'tag'=>'tfoot',   
                    'descendant'=>array(
                        'tag'=>'tr',
                        'descendant'=>array(
                            'tag'=>'td',
                            //'content'=>"{$dataSource[0]['total']}"
                        )
                    )
                ),
            ),            
            $string
        );
    }   
    
    public function testLinkColumn()
    {
        $dataSource = array(
            array('uniqid'=>'123', 'loop_iterator'=> ' times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        
        $table = new GridView\Table($dataSource);
        $table->addColumn(new GridView\Columns\LinkColumn(array(
            'url'=>'/index/view/id/{uniqid}',
            'label'=>'My Butt Hole'
        )));
        
        $string = $table->render();    
        #echo $string;
        $this->assertTag(
            array(
                'tag'=>'td',
                'child'=>array(
                    'tag'=>'a',
                    'attributes'=>array(
                        'href'=>'/index/view/id/123',
                    ),
                    'content'=>'My Butt Hole'
                )
            ),            
            $string
        );
    }
    
    public function testCalcColumn()
    {
        $dataSource = array(
            array('uniqid'=>'123', 'loop_iterator'=>'4 times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        
        $table = new GridView\Table($dataSource);
        $table->addColumn(new GridView\Columns\CalcColumn(array(
            'name'=>'total',
            'calculation'=>function($data){
                return 'hi';//$data['total'] + 100;
            }
        )));
        #echo $dataSource[0]['total'] + 100;
        $string = $table->render();    
        #echo $string;
        $this->assertTag(
            array(
                'tag'=>'td',
                'content'=>'hi',
            ),            
            $string
        );
    }
    
    public function testCheckBoxColumn()
    {
        $dataSource = array(
            array('uniqid'=>'123', 'loop_iterator'=>'4 times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        
        $table = new GridView\Table($dataSource);
        $table->addColumn(new GridView\Columns\CheckBoxColumn(array(
            'name'=>'loop_iterator',
            'checked'=>function($data) {
                return $data['loop_iterator'] == "4 times";
            }
        )));
        $string = $table->render();
        #echo $string;
        $this->assertTag(
            array(
                'tag'=>'input',
                'attributes'=>array(
                    'checked'=>'checked',
                    'value'=>'4 times',
                    'type'=>'checkbox'
                )
            ),            
            $string
        );
    }
    
    public function testDateTimeColumn()
    {
        $dataSource = array(
            array('uniqid'=>'123', 'loop_iterator'=>'4 times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        
        $table = new GridView\Table($dataSource);
        $table->addColumn(new GridView\Columns\DateTimeColumn(array(
            'name'=>'date',            
        )));
        $string = $table->render();
        #echo $string;
        $this->assertTag(
            array(
                'tag'=>'td',
                'content'=>date('Y-m-d 00:00:00')
            ),            
            $string
        );
    }
   
    
    public function testSetSortDirectionFromUrl()
    {
        $dataSource = array(
            array('uniqid'=>'123', 'loop_iterator'=>'4 times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        
        $_GET['sort'] = 'uniqid';
        $_GET['sort_dir'] = 'DESC';
        
        $table = new GridView\Table($dataSource);
        $table->addColumn(array(
            'name'=>'uniqid',
            'sortable'=>true
        ));
        $string = $table->render();
        #echo $string;
        $this->assertTag(array(
            'tag'=>'th',
            'descendant'=>array(
                    'tag'=>'a',
                    'attributes'=>array(
                        'href'=>'?sort=uniqid&sort_dir=ASC',
                        'class'=>'sort-link'
                    ),
                )
            ),
            $string
        );
        
    }
    
    public function testGetTableRowCss()
    {
        $dataSource = array(
            array('uniqid'=>'123', 'loop_iterator'=>'4 times','date'=>date('Y-m-d'),'total'=>rand(1,25))
        );
        
        
        $table = new GridView\Table($dataSource, array(
            'tableRowCss'=>function($data){
                return 'table-row';
            }
        ));
        
        $table->addColumn(array(
            'name'=>'uniqid',
            'sortable'=>true
        ));
        
        $this->assertEquals('table-row', $table->getTableRowCss(array(), 0));
        
        #$string = $table->render();
    }
    
    public function testShowItemsPerPage()
    {
        // not sure why this test fails. its really gay.
        $table = new GridView\Table($this->dataSourceArray, array('showItemsPerPageHeader'=>true));
        $string = $table->renderItemsPerPage();
        #echo $string;
        #return;
        $this->assertTag(array(
            'tag'=>'div',    
            'attributes'=>array(
                    'class'=>'row-fluid'
                ),
            'descendant'=>array(
                    'tag'=>'div',
                    'attributes'=>array(
                        'class'=>'pull-right span3'
                    ),
                    'descendant'=>array(
                        'tag'=>'select',
                        'id'=>'grid-view-limit',
                        'attributes'=>array(
                            'name'=>'limit'
                        ),
                        'children'=>array(
                            'count'=>4,
                            'only'=>array('tag'=>'option')
                        )
                    )
                )
            ),
            $string
        );
    }
    
    public function testSortUrl()
    {
        $table = new GridView\Table($this->dataSourceArray, array('showItemsPerPageHeader'=>true));
        $table->sortUrl = 'index.php';
        
        $url = $table->getSortUrl('uniqid');
        $this->assertEquals('index.php?sort=uniqid&sort_dir=ASC', $url);
        
        $table->sortUrl = 'index.php?nuts=balls';
        
        $url = $table->getSortUrl('uniqid');
        $this->assertEquals('index.php?nuts=balls&sort=uniqid&sort_dir=ASC', $url);
    }

    public function testButton()
    {
        $options = array(
            'url'=>'/',
            'label'=>'{fart} Button!',
            'tokens'=>array('{fart}'=>'Fart')
        );
        $button = new GridView\Buttons\Button($options);
        $string = $button->render();

        $this->assertTag(array(
            'tag'=>'a',
            'content'=>'Fart Button!',
            'attributes'=>array(
                    'class'=>'btn btn-small'
                )
            ), 
            $string
        );
    }

    public function testViewButton()
    {
        $button = new GridView\Buttons\ViewButton('/');
        $string = $button->render();

        $this->assertTag(array(
            'tag'=>'a',
            'content'=>'View',
            'attributes'=>array(
                    'class'=>'btn btn-info',
                    'href'=>'/'
                )
            ), 
            $string
        );
    }

    public function testEditButton()
    {

        $button = new GridView\Buttons\EditButton('/');
        $string = $button->render();

        $this->assertTag(array(
            'tag'=>'a',
            'content'=>'Edit',
            'attributes'=>array(
                    'class'=>'btn btn-success',
                    'href'=>'/'
                )
            ), 
            $string
        );
    }

    public function testDeleteButton()
    {
        $button = new GridView\Buttons\DeleteButton('/');
        $string = $button->render();
        #echo $string;
        $this->assertTag(array(
            'tag'=>'form',            
            'attributes'=>array(
                    'method'=>'post',
                    'action'=>'/',
                    'class'=>'form-inline'
                ),
            'descendant'=>array(
                    'tag'=>'input',
                    'attributes'=>array(
                        'type'=>'submit',
                        'class'=>'btn btn-danger btn-small',
                        'value'=>'Delete',
                        'onclick'=>'return confirm(\'Are you sure you want to do this?\')'
                    )
                )
            ), 
            $string
        );
    }

    public function testButtonColumn()
    {
        $table = new GridView\Table($this->dataSourceArray);
        $table[] = array('name'=>'uniqid');
        $table[] = new GridView\Columns\ButtonColumn(array(
            'buttons'=>array(
                new GridView\Buttons\ViewButton('/view'),
                new GridView\Buttons\EditButton('/edit'),
                new GridView\Buttons\DeleteButton('/delete'),
            )
        ));

        $output = $table->render();
        // will finish in a bit. sorry about that.
        #echo $output;
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column',                
            ),
            'descendant'=>array(
                'tag'=>'a',
                'attributes'=>array(
                    'class'=>'btn btn-info btn-small',
                    'href'=>'/view'
                ),
                'content'=>'View'
            )
        ),
            $output);
        
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column',                
            ),
            'descendant'=>array(
                'tag'=>'a',
                'attributes'=>array(
                    'class'=>'btn btn-success btn-small',
                    'href'=>'/edit'
                ),
                'content'=>'Edit'
            )
           ),
            $output
        );
        
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column'
            ),
            'descendant'=>array(
                'tag'=>'form',
                'attributes'=>array(
                    'action'=>'/delete',
                    'method'=>'post',
                    'class'=>'form-inline'
                ),                
                
                'descendant'=>array(
                    'tag'=>'input',
                    'attributes'=>array(
                        'name'=>'grid-view-submit',
                        'value'=>'Delete',
                        'class'=>'btn btn-danger btn-small',
                        'onclick'=>"return confirm('Are you sure you want to do this?')"
                    ),
                )
            )
            
        ), $output);
    }

    public function testEasyAddButtons()
    {
        $table = new GridView\Table($this->dataSourceArray);
        $table[] = array('name'=>'uniqid');
        $table->addViewButton('/view/id')
        ->addEditButton('/edit/me')
        ->addDeleteButton('/delete/it');
        $output = $table->render();
        #echo $output;
        // will get to the html test soon
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column',                
            ),
            
            'descendant'=>array(
                'tag'=>'a',
                'attributes'=>array(
                    'class'=>'btn btn-info btn-small',
                    'href'=>'/view/id'
                ),
                'content'=>'View'
            )
        ),
        $output);
                
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column',                
            ),
            'descendant'=>array(
                'tag'=>'a',
                'attributes'=>array(
                    'class'=>'btn btn-success btn-small',
                    'href'=>'/edit/me'
                ),
                'content'=>'Edit'
            )
           ),
            $output
        );
        
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column'
            ),
            'descendant'=>array(
                'tag'=>'form',
                'attributes'=>array(
                    'action'=>'/delete/it',
                    'method'=>'post',
                    'class'=>'form-inline'
                ),                
                
                'descendant'=>array(
                    'tag'=>'input',
                    'attributes'=>array(
                        'name'=>'grid-view-submit',
                        'value'=>'Delete',
                        'class'=>'btn btn-danger btn-small',
                        'onclick'=>"return confirm('Are you sure you want to do this?')"
                    ),
                )
            )
            
        ), $output);
    }

    public function testBuildDefaultColumns()
    {        
        $table = new GridView\Table($this->dataSourceArray);
        $table->addViewButton('/view/id')
        ->addEditButton('/edit/me')
        ->addDeleteButton('/delete/it');
        
        $output = $table->render();
        #echo $output;
        #echo 'count is: '. count($this->dataSourceArray[0]);
        $this->assertTag(array(
            'tag'=>'tr',
            'children'=>array(                
                'count'=>(count($this->dataSourceArray[0]) + 1)
            )
        ), $output);
                
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column',                
            ),
            
            'descendant'=>array(
                'tag'=>'a',
                'attributes'=>array(
                    'class'=>'btn btn-info btn-small',
                    'href'=>'/view/id'
                ),
                'content'=>'View'
            )
        ),
        $output);
                
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column',                
            ),
            'descendant'=>array(
                'tag'=>'a',
                'attributes'=>array(
                    'class'=>'btn btn-success btn-small',
                    'href'=>'/edit/me'
                ),
                'content'=>'Edit'
            )
           ),
            $output
        );
        
        $this->assertTag(array(
            'tag'=>'td',
            'attributes'=>array(
                'class'=>'grid-view-button-column'
            ),
            'descendant'=>array(
                'tag'=>'form',
                'attributes'=>array(
                    'action'=>'/delete/it',
                    'method'=>'post',
                    'class'=>'form-inline'
                ),                
                
                'descendant'=>array(
                    'tag'=>'input',
                    'attributes'=>array(
                        'name'=>'grid-view-submit',
                        'value'=>'Delete',
                        'class'=>'btn btn-danger btn-small',
                        'onclick'=>"return confirm('Are you sure you want to do this?')"
                    ),
                )
            )
            
        ), $output);
    }

    public function testListView()
    {
        #print_r($this->dataSourceArray[0]);
        $table = new GridView\TableList($this->dataSourceArray[0]);
        $output = $table->render();
        
        $this->assertTag(array(
            'tag'=>'thead',
            'descendant'=>array(
                'tag'=>'tr',
                'children'=>array(
                    'count'=>2
                )
            )
        ), $output);
        
        $this->assertTag(array(
            'tag'=>'tbody',
            'children'=>array(
                'count'=>count($this->dataSourceArray[0])
            ),
            'descendant'=>array(
                'tag'=>'tr',
                'descendant'=>array(
                    'tag'=>'td'
                ),
                'children'=>array(
                    'count'=>2
                )
            )
        ), $output);
        
    }
}