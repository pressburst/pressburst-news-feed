<?php

	/*
	Plugin Name:  Pressburst News Feed
	Description:  Sync your Pressburst news articles to WordPress
	Version:      1.0
	Author:       Pressburst
	Author URI:   https://pressburst.com
	License:      GPL2
	License URI:  https://www.gnu.org/licenses/gpl-2.0.html
	Text Domain:  pressburst
	*/

	
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'Invalid request.' );
	}


	/* 
	   ========================
	   Styles & Scripts Backend
	   ========================
	*/

	$server_url = sanitize_url($_SERVER["REQUEST_URI"]);

	if (strpos(esc_url($server_url), "pressburst-news-feed")) {
		require_once plugin_dir_path( __FILE__ ) . '/assets/functions/styles-and-scripts-backend.php';
	}



	/* 
	   =========================
	   Styles & Scripts Frontend
	   =========================
	*/

	require_once plugin_dir_path( __FILE__ ) . '/assets/functions/styles-and-scripts-frontend.php';



	/* 
	   ==============
	   Main Functions
	   ==============
	*/

	require_once plugin_dir_path( __FILE__ ) . '/assets/functions/main.php';



	/* 
	   ==============
	   Page Functions
	   ==============
	*/

	require_once plugin_dir_path( __FILE__ ) . '/assets/functions/page.php';
