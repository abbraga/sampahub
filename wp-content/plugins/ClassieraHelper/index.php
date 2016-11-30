<?php
/*
Plugin Name: Classiera Helper
Plugin URI: http://joinwebs.com/
Description: Classiera Custom Post Type for Pricing Plans And Blog Post.
Version: 1.0
Author: JoinWebs
Author URI: http://joinwebs.com/
License: GPLv2
*/
?>
<?php 
//Call Language
	function classiera_helper_textdomain() { 
		load_plugin_textdomain( 'classieraHelper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	add_action( 'plugins_loaded', 'classiera_helper_textdomain' );
?>
<?php
	function post_type_portfolios() {
		$labels = array(
	    	'name' => _x('Pricing Plans', 'post type general name', 'classieraHelper'),
	    	'singular_name' => _x('Price Plans', 'post type singular name', 'classieraHelper'),
	    	'add_new' => _x('Add New Price Plan', 'book', 'classieraHelper'),
	    	'add_new_item' => __('Add New Price Plan', 'classieraHelper'),
	    	'edit_item' => __('Edit Price Plan', 'classieraHelper'),
	    	'new_item' => __('New Price Plan', 'classieraHelper'),
	    	'view_item' => __('View Price Plan', 'classieraHelper'),
	    	'search_items' => __('Search Price Plans', 'classieraHelper'),
	    	'not_found' =>  __('No Price Plan found', 'classieraHelper'),
	    	'not_found_in_trash' => __('No Price Plans found in Trash', 'classieraHelper'), 
	    	'parent_item_colon' => ''
		);		
		$args = array(
	    	'labels' => $labels,
	    	'public' => true,
	    	'publicly_queryable' => true,
	    	'show_ui' => true, 
	    	'query_var' => true,
	    	'rewrite' => true,
	    	'capability_type' => 'post',
	    	'hierarchical' => false,
	    	'menu_position' => null,
	    	'supports' => array('title','editor', 'thumbnail'),
	    	'menu_icon' => get_template_directory_uri().'/images/plans.png'
		); 		

		register_post_type( 'price_plan', $args ); 				  
	} 

	add_action('init', 'post_type_portfolios');


	add_action( 'add_meta_boxes', 'plan_ads_box' );
	function plan_ads_box() {
	    add_meta_box( 
	        'plan_ads_box',
	        __( 'Featured Ads', 'classiera' ),
	        'plan_ads_content',
	        'price_plan',
	        'side',
	        'high'
	    );
	}

	function plan_ads_content( $post ) {

		$featured_ads = get_post_meta( $post->ID, 'featured_ads', true );

		echo '<label for="featured_ads"></label>';
		echo '<input type="text" id="featured_ads" name="featured_ads" placeholder="Leave empty for unlimited" value="';
		echo $featured_ads; 
		echo '">';
		
	}

	add_action( 'save_post', 'project_link_box_save' );
	function project_link_box_save( $post_id ) {		

		global $featured_ads;

		if(isset($_POST["featured_ads"]))
		$featured_ads = $_POST['featured_ads'];
		update_post_meta( $post_id, 'featured_ads', $featured_ads );

	}
/*Plans Titles*/
add_action( 'add_meta_boxes', 'plan_text_box' );
	function plan_text_box() {
	    add_meta_box( 
	        'plan_text_box',
	        __( 'Replace your text line with default one', 'classieraHelper' ),
	        'plan_text_content',
	        'price_plan'
	    );
	}
	
	function plan_text_content( $post ) {

		$plan_text = get_post_meta( $post->ID, 'plan_text', true );

		echo '<label for="plan_text"></label>';
		echo '<input type="text" id="plan_text" name="plan_text" placeholder="Get Started free trial with ClassiEra" value="';
		echo $plan_text; 
		echo '">';
		
	}
	
	add_action( 'save_post', 'plan_text_save' );
	function plan_text_save( $post_id ) {		

		global $plan_text;

		if(isset($_POST["plan_text"]))
		$plan_text = $_POST['plan_text'];
		update_post_meta( $post_id, 'plan_text', $plan_text );

	}
	/*Free Ads Posting*/
	add_action( 'add_meta_boxes', 'plan_free_text_box' );
	function plan_free_text_box() {
	    add_meta_box( 
	        'plan_free_text_box',
	        __( 'Replace your text line with default one', 'classieraHelper' ),
	        'plan_free_text_content',
	        'price_plan'
	    );
	}

	function plan_free_text_content( $post ) {
		wp_nonce_field( 'plantextfree_meta_box', 'plantextfree_meta_box_nonce' );
		$plan_free_text = get_post_meta( $post->ID, 'plan_free_text', true );

		echo '<label for="plan_free_text"></label>';
		echo '<input type="text" id="plan_free_text" name="plan_free_text" placeholder="Featured ad posting" value="';
		echo $plan_free_text; 
		echo '">';
		
	}


	add_action( 'save_post', 'plan_free_text_save' );
	function plan_free_text_save( $post_id ) {		

		global $plan_free_text;
		
		if ( ! isset( $_POST['plantextfree_meta_box_nonce'] ) ) {
		return;
		}
		if ( ! wp_verify_nonce( $_POST['plantextfree_meta_box_nonce'], 'plantextfree_meta_box' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if(isset($_POST["plan_free_text"]))
		$plan_free_text = $_POST['plan_free_text'];
		update_post_meta( $post_id, 'plan_free_text', $plan_free_text );

	}
	/*Free Ads Posting*/
	/*100% Secure*/
	add_action( 'add_meta_boxes', 'plan_secure_text_box' );
	function plan_secure_text_box() {
	    add_meta_box( 
	        'plan_secure_text_box',
	        __( 'Replace your text line with default one', 'classieraHelper' ),
	        'plan_secure_text_content',
	        'price_plan'
	    );
	}

	function plan_secure_text_content( $post ) {
		wp_nonce_field( 'plantextsecure_meta_box', 'plantextsecure_meta_box_nonce' );
		$plan_secure_text = get_post_meta( $post->ID, 'plan_secure_text', true );

		echo '<label for="plan_secure_text"></label>';
		echo '<input type="text" id="plan_secure_text" name="plan_secure_text" placeholder="100% Secure!" value="';
		echo $plan_secure_text; 
		echo '">';
		
	}


	add_action( 'save_post', 'plan_secure_text_save' );
	function plan_secure_text_save( $post_id ) {		

		global $plan_secure_text;
		
		if ( ! isset( $_POST['plantextsecure_meta_box_nonce'] ) ) {
		return;
		}
		if ( ! wp_verify_nonce( $_POST['plantextsecure_meta_box_nonce'], 'plantextsecure_meta_box' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if(isset($_POST["plan_secure_text"]))
		$plan_secure_text = $_POST['plan_secure_text'];
		update_post_meta( $post_id, 'plan_secure_text', $plan_secure_text );

	}
	/*100% Secure*/
/*Plans Titles*/

	add_action( 'add_meta_boxes', 'plan_price_box' );
	function plan_price_box() {
	    add_meta_box( 
	        'plan_price_box',
	        __( 'Price :Dont put Cuccreny tag', 'classieraHelper' ),
	        'plan_price_content',
	        'price_plan',
	        'side',
	        'high'
	    );
	}

	function plan_price_content( $post ) {

		$plan_price = get_post_meta( $post->ID, 'plan_price', true );

		echo '<label for="plan_price"></label>';
		echo '<input type="text" id="plan_price" name="plan_price" placeholder="Like 5" value="';
		echo $plan_price; 
		echo '">';
		
	}


	add_action( 'save_post', 'plan_price_save' );
	function plan_price_save( $post_id ) {		

		global $plan_price;

		if(isset($_POST["plan_price"]))
		$plan_price = $_POST['plan_price'];
		update_post_meta( $post_id, 'plan_price', $plan_price );

	}



	add_action( 'add_meta_boxes', 'plan_time_box' );
	function plan_time_box() {
	    add_meta_box( 
	        'plan_time_box',
	        __( 'Days', 'classiera' ),
	        'plan_time_content',
	        'price_plan',
	        'side',
	        'high'
	    );
	}

	function plan_time_content( $post ) {

		$plan_time = get_post_meta( $post->ID, 'plan_time', true );

		echo '<label for="plan_time"></label>';
		echo '<input type="text" id="plan_time" name="plan_time" placeholder="Leave empty for unlimited" value="';
		echo $plan_time; 
		echo '">';
		
	}


	add_action( 'save_post', 'plan_time_save' );
	function plan_time_save( $post_id ) {		

		global $plan_time;

		if(isset($_POST["plan_time"]))
		$plan_time = $_POST['plan_time'];
		update_post_meta( $post_id, 'plan_time', $plan_time );

	}
	
	
	
	add_action( 'add_meta_boxes', 'plan_user_box_cancel' );
	function plan_user_box_cancel() {
	    add_meta_box( 
	        'plan_user_box_cancel',
	        __( 'Put here USERNAME to cancel this plan for particular user (Leave empty for nothing)', 'classieraHelper' ),
	        'plan_user_content_cancel',
			'price_plan'

	    );
	}

	function plan_user_content_cancel( $post ) {
		
		echo '<label for="plan_cancel"></label>';
		echo '<input type="text" id="plan_cancel" name="plan_cancel" placeholder="USERNAME" value="';

		echo '">';
	}
	
	add_action( 'save_post', 'plan_user_del_save' );
	function plan_user_del_save( $post_id ) {
		global $wpdb;
		if (isset($_POST['plan_cancel'])) {
		$plan_cancel = $_POST['plan_cancel'];
			if(!empty($plan_cancel)){
				$user = get_user_by( 'login', $plan_cancel );		
				$user_cancel = $user->ID;
				$posttitle  = get_the_title($post->ID);
				$result = $wpdb->get_results( "SELECT * FROM wpcads_paypal WHERE user_id = $user_cancel AND name = '$posttitle'  ORDER BY main_id DESC" );

					if (!empty($result )) {
						
					  foreach ( $result as $key => $row ) {
						  
								if($row->ads == '0'){
									$wpdb->update('wpcads_paypal', array('ads'=> '2', 'used'=>'2'), array( 'main_id'=>$row->main_id) );

								}else{
								$wpdb->update('wpcads_paypal', array('used'=>$row->ads), array( 'main_id'=>$row->main_id) );
								}

		
						}
					  
					}
			}	
		}
	}
	
	
	
	
	
	
	add_action( 'add_meta_boxes', 'plan_user_box' );
	function plan_user_box() {
	    add_meta_box( 
	        'plan_user_box',
	        __( 'Put here USERNAME to assign this plan for particular user (Leave empty for nothing)', 'classieraHelper' ),
	        'plan_user_content',
			'price_plan'

	    );
	}

	function plan_user_content( $post ) {
		
		echo '<label for="plan_add"></label>';
		echo '<input type="text" id="plan_add" name="plan_add" placeholder="USERNAME" value="';

		echo '">';
	}
	
	add_action( 'save_post', 'plan_user_add_save' );
	function plan_user_add_save( $post_id ) {
				global $plan_time;
				global $featured_ads;
				global $plan_price;
				global $post_title;
				global $wpdb;
		if (!empty($_POST['plan_add'])) {
		$plan_add = $_POST['plan_add'];
		$user = get_user_by( 'login', $plan_add );
		$user_add = $user->ID;
		$posttitle  = get_the_title($post->ID);


			$price_plan_information = array(
				'id' => '',
				'user_id' => $user_add,
				'name' => $posttitle,
				'token' => "",
				'price' => $plan_price,
				'currency' => "",
				'ads' => $featured_ads,
				'days' => $plan_time,
				'date' => date("m/d/Y H:i:s"),
				'status' => "success",
				'used' => "0",
				'transaction_id' => "",
				'firstname' => "",
				'lastname' => "",
				'email' => "",
				'description' => "",
				'summary' => "",
				'created' => time()
			  ); 

			  $insert_format = array('%s', '%s', '%s','%s', '%f', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s');
				$result = $wpdb->get_results( "SELECT * FROM wpcads_paypal WHERE user_id = $plan_add AND name = '$posttitle'  ORDER BY main_id DESC" );

				if (empty($result )) {
				$wpdb->insert('wpcads_paypal', $price_plan_information, $insert_format);
				}
		}
	}
	
	add_action( 'add_meta_boxes', 'popular_plan_box' );
	function popular_plan_box() {
	    add_meta_box( 
	        'popular_plan',
	        __( 'Most popular plan', 'agrg' ),
	        'popular_plan',
			'price_plan'

	    );
	}
	function popular_plan( $post ) {
		
		echo '<label for="popular_plan"></label>';
		echo '<input type="checkbox" id="popular_plan" name="popular_plan" value="true" />';
	}
	
	add_action( 'save_post', 'popular_plan_save' );
	function popular_plan_save( $post_id ) {
		global $wpdb;
		if (isset($_POST['popular_plan']) && !empty($_POST['popular_plan'])) {
			$plan_pop = $_POST['popular_plan'];			
		}else{
			$plan_pop = '';
			
		}
		update_post_meta( $post_id, 'popular_plan', $plan_pop );
	}

/* Register Blog Post Type*/
function blog_categories_fc() {
	register_taxonomy(
		'blog_categories',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
		'blog_posts',   		 //post type name
		array(
			'hierarchical' 		=> true,
			'label' 			=> 'Categories',  //Display name
			'query_var' 		=> true,
			'rewrite'			=> array(
					'slug' 			=> 'blog', // This controls the base slug that will display before each term
					'with_front' 	=> false // Don't display the category base before
					)
			)
		);
}
add_action( 'init', 'blog_categories_fc');

function filter_post_type_link( $link, $post) {
    if ( $post->post_type != 'blog_posts' )
        return $link;

    if ( $cats = get_the_terms( $post->ID, 'blog_categories' ) )
        $link = str_replace( '%blog_categories%', array_pop($cats)->slug, $link );
    return $link;
}
add_filter('post_type_link', 'filter_post_type_link', 10, 2);	

	function blog_post_type() {
		$labels = array(
	    	'name' => _x('Blog Posts', 'post type general name', 'classieraHelper'),
	    	'singular_name' => _x('Blog Posts', 'post type singular name', 'classieraHelper'),
	    	'add_new' => _x('Add New Blog Post', 'book', 'classieraHelper'),
	    	'add_new_item' => __('Add New Blog Post', 'classieraHelper'),
	    	'edit_item' => __('Edit Blog Post', 'classieraHelper'),
	    	'new_item' => __('New Blog Post', 'classieraHelper'),
	    	'view_item' => __('View Blog Post', 'classieraHelper'),
	    	'search_items' => __('Search Blog Posts', 'classieraHelper'),
	    	'not_found' =>  __('No Blog Post found', 'classieraHelper'),
	    	'not_found_in_trash' => __('No Blog Post found in Trash', 'classieraHelper'), 
	    	'parent_item_colon' => ''
		);		
		$args = array(
	    	'labels' => $labels,
	    	'public' => true,
	    	'publicly_queryable' => true,
	    	'show_ui' => true, 
	    	'query_var' => true,	    	
	    	'capability_type' => 'post',
			'has_archive' => 'Blog',
	    	'hierarchical' => false,
	    	'menu_position' => null,
	    	'supports' => array('title','editor', 'thumbnail', 'comments', 'custom-fields'),
			'taxonomies' => array('post_tag', 'blog_categories'),			
			'rewrite' => array('slug' => 'blog-posts','with_front' => FALSE),
	    	'menu_icon' => 'dashicons-admin-post'
		); 		

		register_post_type( 'blog_posts', $args );
		flush_rewrite_rules(true);
	} 

	add_action('init', 'blog_post_type');
	//flush_rewrite_rules();
include_once('shortcode.lib.php');	
include_once('twitter-function.php');
include_once('paypalapi.php');			
?>