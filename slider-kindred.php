<?php

/*
Plugin Name: Slider Kindred Plugin
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.0
Author: mauricio
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/


defined('ABSPATH') or die('Hey, what are you doing here? You silly human!');

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
	require_once dirname(__FILE__) . '/vendor/autoload.php';
}

use Inc\Activate;
use Inc\Backend;
use Inc\Deactivate;
use Inc\Frontend;

if (!class_exists('sliderKindredPluging')) {
	class sliderKindredPluging
	{
		
		protected string $plugin_name;
		protected string $plugin_url;
		
		
		public function __construct()
		{
			$this->plugin_url = plugin_basename(__FILE__);
			$this->plugin_name = 'slider-kindred';
			
			require_once dirname( __FILE__ )  . '/inc/Backend.php';
			require_once dirname( __FILE__ )  . '/inc/Frontend.php';
			
		}
		
		public function run(){
			$this->define_frontend_hooks();
			$this->define_backend_hooks();
		}
		
		/**
		 * Register all of the hooks related to the dashboard functionality of the plugin.
		 */
		private function define_frontend_hooks() {
		$frontend = new Frontend($this->plugin_name);
			add_shortcode('mycarousel', [$frontend, 'build_slider']);
			add_action('init', [$frontend, 'custom_post_type']);
			add_action('wp_enqueue_scripts', [$frontend, 'enqueue']);
			
			
			add_action( 'add_meta_boxes',[ $frontend, 'add_meta_box'] );
			add_action( 'save_post', [$frontend, 'save_meta_box'] );
		}
		
		private function define_backend_hooks() {
			$backend = new Backend($this->plugin_name);
		}
		
		
		function activate()
		{
			Activate::activate();
		}
		
		function deactivate()
		{
			Deactivate::deactivate();
		}
		
		
	}
	
	$sliderKindredPluging = new sliderKindredPluging();
	$sliderKindredPluging->run();
	
	// activation
	register_activation_hook(__FILE__, [
		$sliderKindredPluging,
		'activate'
	]);
	
	// deactivation
	register_deactivation_hook(__FILE__, [
		$sliderKindredPluging,
		'deactivate'
	]);
}
