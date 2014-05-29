<?php namespace GridView\Buttons;

interface ButtonInterface {	
	public function render();
	public function getUrl($data);
	public function getLabel($data);
    public function isVisible();
}