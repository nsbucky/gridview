<?php namespace GridView\Columns;

interface ColumnInterface {
	public function getValue($index);
	public function setData($data);
	public function getFilter();
	public function getHeader();
	public function isVisible();
	public function getFooter();
	public function setTable(\GridView\Table $table);
	public function tokenize($data);
	public function replaceTokens($string);
}