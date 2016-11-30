<?php
/*
Plugin Name: Classiera Locations
Plugin URI: http://joinwebs.com/
Description: Add Countries Satates And City Only for Classiera.
Version: 1.0
Author: JoinWebs
Author URI: http://joinwebs.com/
License: GPLv2
*/
?>
<?php 
	//Call Requried Files
	require_once ( plugin_dir_path( __FILE__ ) . 'includes/country.php' );
	require_once ( plugin_dir_path( __FILE__ ) . 'includes/state.php' );
	require_once ( plugin_dir_path( __FILE__ ) . 'includes/city.php' );
	
	//Call Language
	function locations_jw_textdomain() { 
		load_plugin_textdomain( 'classiera-locations', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	add_action( 'plugins_loaded', 'locations_jw_textdomain' );
	
	//Hide Add New In Country
	function classiera_country_add_new_custom_type(){
		global $submenu;
		// replace my_type with the name of your post type
		unset($submenu['edit.php?post_type=countries'][10]);
	}
	add_action('admin_menu', 'classiera_country_add_new_custom_type');
	
	//Call Requried Scripts
	if(!function_exists('load_csc_scripts')){
		function load_csc_scripts(){
			if (is_admin()) {

				// Defining scripts directory url
				$classiera_script_url = plugin_dir_url( __FILE__ ).'js/';

				// Custom Script
				wp_register_script('jquery.validate.min',$classiera_script_url.'jquery.validate.min.js', array('jquery'));
				wp_register_script('custom',$classiera_script_url.'custom.js', array('jquery'), '1.0', true);

				// Enqueue Scripts that are needed on all the pages
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery.validate.min');
				wp_enqueue_script('custom');
			}
		}
	}
	add_action('admin_enqueue_scripts', 'load_csc_scripts');
	
	//Get States on Country select
	add_action( 'wp_ajax_get_states_of_country', 'get_states_of_country' );
	add_action( 'wp_ajax_nopriv_get_states_of_country', 'get_states_of_country' );
	function get_states_of_country(){
		global $wpdb; 
		$cid = intval( $_POST['CID'] );
		$state_posts = get_posts( array( 'post_type' => 'states', 'posts_per_page' => -1, 'suppress_filters' => 0, 'meta_query' => array(
			array(
				'key' => 'state_meta_box_country',
				'value' => $cid,
			)
		) ) );
		
		if(!empty($state_posts)){		
			foreach( $state_posts as $state_post ){
				$state = $state_post->ID;					
				$statesList .= get_post_meta($state, "classiera-all-states", true).",";				
			}
		}
		//print_r($statesList);
		$singleState = explode(",", $statesList);
		//print_r($singleState);
		/*get Custom meta for states*/
		$state_ops = '<option value="All">'.__('Select State', 'classiera-locations')."</option>";
		if(!empty($singleState)){
			foreach( $singleState as $state_post ){
				if(!empty($state_post)){
					$state_ops .= '<option value="'.$state_post.'">'.$state_post."</option>";
				}
			}
		}
		echo $state_ops;
		die(); // this is required to terminate immediately and return a proper response
	}
	//Get States on Country select
	//Get City on select State
	add_action( 'wp_ajax_get_city_of_states', 'get_city_of_states' );
	add_action( 'wp_ajax_nopriv_get_city_of_states', 'get_city_of_states' );
	function get_city_of_states(){
		global $wpdb;
		$cid = $_POST['ID'];		
		$city_posts = get_posts( array( 'post_type' => 'cities', 'posts_per_page' => -1, 'suppress_filters' => 0, 'meta_query' => array(
			array(
				'key' => 'city_meta_box_state',
				'value' => $cid,
			)
		) ) );
		/*Get CityList*/
		if(!empty($city_posts)){		
			foreach( $city_posts as $city_post ){
				$city = $city_post->ID;					
				$cityList .= get_post_meta($city, "classiera-all-city", true).",";				
			}
		}
		//print_r($cityList);
		$singlecity = explode(",", $cityList);
		//print_r($singlecity);
		/*Get CityList*/
		$state_ops = '<option value="All">'.__('Select City' , 'classiera-locations')."</option>";
		if(!empty($singlecity)){
			foreach( $singlecity as $city_post ){
				if(!empty($city_post)){
					$state_ops .= '<option value="'.$city_post.'">'.$city_post."</option>";
				}
			}
		}
		echo $state_ops;
		die();
		
	}
	//Get City on select State
	//Classiera Search Function
	add_action( 'wp_ajax_get_search_classiera', 'get_search_classiera' );
	add_action( 'wp_ajax_nopriv_get_search_classiera', 'get_search_classiera' );
	function get_search_classiera(){	
		$args = array( 
			'post_type' => 'post',
			'post_status' => 'publish',
			'order' => 'DESC',
			'orderby' => 'date',
			's' =>$_POST['CID'],
			'posts_per_page' =>5,	 
		);
		$query = new WP_Query( $args );
		if($query->have_posts()){
			while ($query->have_posts()) {
				$query->the_post();
			?>
			<!--<li><?php the_title(); ?></li>-->
			<li><a href="#" name="<?php the_title(); ?>" class="SearchLink"><?php the_title(); ?></a></li>
			
			<?php
			}
		}else{
			?>
			<li><a href="#">.<?php esc_html_e( 'No Result found related your search', 'classiera-locations' );?></a></li>
			<?php 
		}exit();	
	}
	//Classiera Search Function	
?>