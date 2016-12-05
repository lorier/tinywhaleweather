<?php

// enqueue the child theme stylesheet

Function wp_schools_enqueue_scripts() {
wp_register_style( 'childstyle', get_stylesheet_directory_uri() . '/style.css'  );
wp_enqueue_style( 'childstyle' );
}
add_action( 'wp_enqueue_scripts', 'wp_schools_enqueue_scripts', 11);

//deregister the original 'default.min.js' script so we can create our own.
function tw_deregister_stockholm_js(){
	wp_deregister_script('qode_default');
	wp_dequeue_script('qode_default');
}
add_action( 'wp_print_scripts', 'tw_deregister_stockholm_js', 100);

//register the new default script
function tw_register_stockholm_js(){
	wp_register_script( 'default', get_stylesheet_directory_uri() . '/js/default.js', array(), false  );
	wp_enqueue_script( 'default' );
}
add_action( 'wp_enqueue_scripts', 'tw_register_stockholm_js', 10);



////////////////////
//// Weather API 
////////////////////

// Code based on https://wordpress.org/plugins/wunderground/

//from Wunderground_Display

function lr_add_scripts(){

	wp_enqueue_script( 'weather', get_stylesheet_directory_uri() . '/js/weather.js');
	// Localize script - make any data available to your script that you can normally only get from the server side of WordPress
	wp_localize_script( 'weather', 'Wapp', array(
		'_wpnonce' => wp_create_nonce('lr_wapp_nc'),
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'is_admin' => is_admin()
	));
}
add_action('wp_enqueue_scripts', 'lr_add_scripts');


//from Wunderground_Ajax class

add_action( 'wp_ajax_lr_wapp', 'lr_weather_data'  ); //The wp_ajax_ hook follows the format "wp_ajax_$youraction", where $youraction is your AJAX request's 'action' property
add_action( 'wp_ajax_nopriv_lr_wapp', 'lr_weather_data' ); //handle AJAX requests on the front-end for unauthenticated users


function lr_weather_data() {
	if(!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'lr_wapp_nc')) {
		exit(0);
	}
	
	//local for testing
	// $url = add_query_arg( array(
	// 	'query' => urlencode( stripslashes_deep( $_REQUEST['query'] ) ),
	// ), 'http://localhost:3000/weather' );

	$url = 'http://api.openweathermap.org/data/2.5/weather?id=5809844&APPID=b9c8b98edac6dbd2d1d24df4fbad6072&units=imperial' ;

	$response = lr_request( $url );

	exit($response);
}

//From Wunderground_Request


$cache = false;
	/**
	 * Fetch a URL and use/store cached result
	 *
	 * - Cached results are stored as transients starting with `lru_`
	 * - Results are stored for twenty minutes.
	 * - The request array itself can be filtered by using the `wunderground_request_atts` filter
	 *
	 * @filter  wunderground_cache_time description
	 * @param  [type]  $url   [description]
	 * @param  boolean $cache [description]
	 * @return [type]         [description]
	 */
function lr_request($url, $cache = true) {
	// Generate a cache key based on the result. Only get the first 44 characters because of
	// the transient key length limit.
	$cache_key = substr( 'lr_'.sha1($url) , 0, 44 );

	// for dev purposes - quickly remove transient rows from db
	// delete_transient( $cache_key );

	$response = get_transient( $cache_key );

	// for testing - display 'cached' at end of json in console 
	// if(!empty( $response )){
	// 	$response .= 'cached';
	// }

	// If there's no cached result or caching is disabled
	if( empty( $cache ) || empty( $response ) ) {		
		/**
		 * Modify the request array. By default, only sets timeout (10 seconds)
		 * @var array
		 */
		$atts = apply_filters( 'lr_wapp_request_atts', array(
			'timeout' => 10
		));
		$request = wp_remote_request( $url , $atts );
		if( is_wp_error( $request ) ) {
			$response = false;
			
		} else {
			$response = wp_remote_retrieve_body( $request );

			/**
			 * Modify the number of seconds to cache the request for.
			 *
			 * Default: cache the request ten minutes, since we're dealing with changing conditions
			 *
			 * @var int
			 */

			$cache_time = 10*MINUTE_IN_SECONDS;
			set_transient( $cache_key, $response, (int)$cache_time );
			
		}
	}
	return stripslashes_deep( $response );
}


