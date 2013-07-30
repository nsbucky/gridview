<?php namespace GridView\Columns;

interface ColumnInterface {
	public function getValue($data, $index);
	public function getFilter();
	public function getHeader();
	public function isVisible();
	public function getFooter();
	public function setTable(\GridView\Table $table);
	public function tokenize($data);
}