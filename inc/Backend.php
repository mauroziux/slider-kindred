<?php

namespace Inc;

class Backend
{
	protected $plugin_name;
	
	public function __construct($plugin_name)
	{
		$this->plugin_name = $plugin_name;
		
	}
}