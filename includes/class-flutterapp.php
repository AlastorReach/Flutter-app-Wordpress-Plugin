<?php

final class FlutterApp {
	
	protected static $_instance = null;
	
	public $version = '1.0.0';
	
	public function __construct(){
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}
	
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function saluda(){
		echo "Hola Josue";
	}
	
	private function define_constants() {

		$this->define( 'FA_ABSPATH', dirname( FA_PLUGIN_FILE ) . '/' );
		$this->define( 'FA_PLUGIN_BASENAME', plugin_basename( FA_PLUGIN_FILE ) );
		$this->define( 'FA_VERSION', $this->version );
        $this->define( 'FA_PLUGIN_ROOT', str_replace('includes/', '', plugin_dir_url( __FILE__ )));
        $this->define('FA_ACTIVE_CATEGORIES', 'active_categories/v1/categories');
		
	}
	
	public function includes() {
        
                
		include_once FA_ABSPATH . 'includes/class-wp-rest-posts-mobile-custom-controller.php';
		include_once FA_ABSPATH . 'includes/class-wp-rest-posts-wifi-custom-controller.php';
		include_once FA_ABSPATH . 'includes/class-wp-rest-active-terms-custom-controller.php';
		include_once FA_ABSPATH . 'includes/class-fa-custom-database-tables.php';
        include_once FA_ABSPATH . 'includes/class_fa_admin_menu.php';
        include_once FA_ABSPATH . 'includes/class_fa_add_css_to_admin_pages.php';
        include_once FA_ABSPATH . 'includes/class-fa-add-custom-meta-to-categories.php';
		include_once FA_ABSPATH . 'includes/class-fa-add-custom-post-types.php';
		include_once FA_ABSPATH . 'functions/function_rename_custom_post_fields.php';
		include_once FA_ABSPATH . 'includes/class_fa_add_metabox_to_custom_post_type.php';
		include_once FA_ABSPATH . 'functions/function_send_push_notification_on_new_post_published.php';
		include_once FA_ABSPATH . 'functions/functions.php';
                
		
	}
	
	private function init_hooks() {
		
		add_option( 'flutter_categories_version', '1', '', 'yes' );
		add_option( 'flutter_envivo_url', '', '', 'yes' );
		add_option( 'flutter_firebase_api_key', '', '', 'yes' );
		add_option( 'flutter_send_push_notifications', true, '', 'yes' );
		add_option( 'flutter_total_amount_of_news_stored_in_database_per_category', 10, '', 'yes' );
		add_option( 'flutter_total_amount_of_news_per_request', 10, '', 'yes' );
		add_option( 'flutter_max_number_of_pages_on_api_request', 5, '', 'yes' );
		add_option( 'flutter_email_for_notifications', '', '', 'yes' );
		
		register_activation_hook( __FILE__, array( 'FA_Custom_Database_Tables', 'FA_create_tables' ));
		add_theme_support('post-thumbnails');
		add_image_size('flutter_app_mobile', 500, 375, true);
		add_image_size('flutter_app_tablet', 768, 576, true);
		add_image_size('flutter_app_tablet_pro', 1024, 768, true);
		
		//Custom posts endpoint -> wp-json/mobile/v1/posts
		add_action( 'rest_api_init', array( $this, 'register_custom_rest_routes_for_posts' ), -1 );
		add_filter( 'rest_prepare_post_mobile', array($this,'get_all_posts_low_consume'), 10, 3 );
		
		add_action('rest_api_init', array( $this, 'register_custom_rest_routes_for_posts_wifi'), -1 );
		add_filter( 'rest_prepare_post_wifi', array($this,'get_all_posts_wifi'), 10, 3 );
		
		//Custom endpoint -> wp-json/active_categories/v1/categories
		add_action( 'rest_api_init', array( $this, 'register_custom_rest_routes_for_active_categories' ), -1 );
		add_filter( 'rest_prepare_active_categories', array($this,'get_active_categories_for_app'), 10, 3 );
		
		add_action('rest_api_init', function () {
			register_rest_route( 'envivo/v1', 'info',array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_envivo_info'),
			));
		});
		
		add_action('rest_api_init', function () {
			register_rest_route( 'ads/v1', 'track/(?P<track>\w+)',array(
                'methods'  => 'GET',
                'callback' => array($this, 'test_track_click_ad'),
			));
		});
		
		add_action('rest_api_init', function () {
			register_rest_route( 'tv/upcoming_events', '3-events/',array(
                'methods'  => 'GET',
                'callback' => array($this, 'get_the_upcoming_events'),
			));
		});
		
		add_action('rest_api_init', function () {
			register_rest_route( 'tv/upcoming_events', 'all-events/',array(
                'methods'  => 'GET',
                'callback' => array($this, 'getAllUpcomingEvenstByThisDay'),
			));
		});
		
		add_action("jwt_auth_expire", array($this, "changeTokenExpiration"));
		
		add_filter( 'posts_search', array($this,'__search_by_title_only'), 500, 2 );
		

	}
	
	function __search_by_title_only( $search, &$wp_query )
{
    global $wpdb;

    if ( empty( $search ) )
        return $search; // skip processing - no search term in query

    $q = $wp_query->query_vars;    
    $n = ! empty( $q['exact'] ) ? '' : '%';

    $search =
    $searchand = '';

    foreach ( (array) $q['search_terms'] as $term ) {
        $term = esc_sql( like_escape( $term ) );
        $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
        $searchand = ' AND ';
    }

    if ( ! empty( $search ) ) {
        $search = " AND ({$search}) ";
        if ( ! is_user_logged_in() )
            $search .= " AND ($wpdb->posts.post_password = '') ";
    }

    return $search;
}

	
	function get_the_upcoming_events(){
		$events = get_upcoming_events("MOST_RECENT_3_EVENTS");
		$events = new WP_REST_Response($events);
		return $events;
	}
	
	function getAllUpcomingEvenstByThisDay(){
		$events = get_upcoming_events("ALL_EVENTS_BY_THIS_DAY");
		$categories_version = get_option( 'flutter_categories_version');
		$news_on_db = get_option('flutter_total_amount_of_news_stored_in_database_per_category');
		$news_per_request = get_option('flutter_total_amount_of_news_per_request');
		$max_of_pages = get_option('flutter_max_number_of_pages_on_api_request');
		$envivo_url = get_option('flutter_envivo_url');
		$data = array(
		"cat_version" => intval($categories_version),
		"news_stored_in_database" => intval($news_on_db),
		"news_per_request" => intval($news_per_request),
		"max_of_pages" => intval($max_of_pages),
		"uev" => $envivo_url,
		"events" => $events,
		);
		$events = new WP_REST_Response($data);
		return $events;
	}
	
	function register_custom_rest_routes_for_posts() {
		
		$controller = new WP_REST_posts_mobile_custom_controller();
		$controller->register_routes();
	}
	function register_custom_rest_routes_for_posts_wifi(){
		$controller = new WP_REST_posts_wifi_custom_controller();
		$controller->register_routes();
	}
	
	function register_custom_rest_routes_for_active_categories() {
		$controller = new WP_REST_Terms_Custom_Controller('category');
		$controller->register_routes();
	}
	
	///Esta función especifica cuales campos deben salir en la respuesta a la petición wp-json/wp/v2/posts
function get_all_posts_low_consume( $data, $post, $context ) {
	$featured_image_id = $data->data['featured_media'];
	$mobile = wp_get_attachment_image_src( $featured_image_id, 'flutter_app_mobile' );
	$tablet = wp_get_attachment_image_src( $featured_image_id, 'flutter_app_tablet' );
	$tabletPro = wp_get_attachment_image_src( $featured_image_id, 'flutter_app_tablet_pro' );
	$thumbnail = wp_get_attachment_image_src( $featured_image_id, 'thumbnail' );
	$medium = wp_get_attachment_image_src( $featured_image_id, 'medium' );
	$large = wp_get_attachment_image_src( $featured_image_id, 'large' );
	$original = wp_get_attachment_image_src( $featured_image_id, 'original' );
	$tags = get_the_tags( $post->id );
	$top_level_categories = array();
	$tagNames = array();
	$categoriesName = array();
	if(!empty($tags)){
		foreach($tags as $tag){
			array_push($tagNames, $tag->name);
		}
	}
	$categories = get_the_category($post->id);
	if(!empty($categories)){
		foreach($categories as $category){
			if($category->parent === 0){
				array_push($top_level_categories, $category->name);
			}else{
			array_push($categoriesName, $category->name);
			}
		}
	}
	
	//Estos son los campos que saldrán como respuesta

	return [
		'id'		=> $data->data['id'],
		'date'      => $data->data['date'],
		'date_gmt'  => $data->data['date_gmt'],
		'modified'  => $data->data['modified'],
		'title'    	=> $data->data['title']['rendered'],
		'link'     	=> $data->data['link'],
		'thumbnail' => $thumbnail[3] ? $thumbnail[0] : "",
		'medium'    => $medium[3] ? $medium[0] : "",
		'large'     => $large[3] ? $large[0] : "",
		'mobile'    => $mobile[3] ? $mobile[0] : "",
		'tablet'    => $tablet[3] ? $tablet[0] : "",
		'tabletPro'    => $tabletPro[3] ? $tabletPro[0] : "",
		'categories'  => $categoriesName,
		"topLevelCategories" => $top_level_categories,
		'tags'      => $tagNames,
		
	];
}

function get_all_posts_wifi($data, $post, $context ){
	$featured_image_id = $data->data['featured_media'];
	$thumbnail = wp_get_attachment_image_src( $featured_image_id, 'thumbnail' );
	$medium = wp_get_attachment_image_src( $featured_image_id, 'medium' );
	$mobile = wp_get_attachment_image_src( $featured_image_id, 'flutter_app_mobile' );
	$tablet = wp_get_attachment_image_src( $featured_image_id, 'flutter_app_tablet' );
	$tabletPro = wp_get_attachment_image_src( $featured_image_id, 'flutter_app_tablet_pro' );
	$mediumLarge = wp_get_attachment_image_src( $featured_image_id, 'mediumLarge' );
	$large = wp_get_attachment_image_src( $featured_image_id, 'large' );
	$original = wp_get_attachment_image_src( $featured_image_id, 'original' );
	$tags = get_the_tags( $post->id );
	$tagNames = array();
	$author_id = $post->post_author; 
	$top_level_categories = array();
	$categoriesName = array();
	if(!empty($tags)){
		foreach($tags as $tag){
			array_push($tagNames, $tag->name);
		}
	}
	$categories = get_the_category($post->id);
	if(!empty($categories)){
		foreach($categories as $category){
			if($category->parent === 0){
				array_push($top_level_categories, $category->name);
			}else{
			array_push($categoriesName, $category->name);
			}
		}
	}
	
	return [
		'id'		=> $data->data['id'],
		'date'      => $data->data['date'],
		'date_gmt'      => $data->data['date_gmt'],
		'modified'  => $data->data['modified'],
		'title'    	=> $data->data['title']['rendered'],
		'content'   => $data->data['content']['rendered'],
		'excerpt'   => $data->data['excerpt']['rendered'],
		'author'    => get_the_author($post->id),
		'avatar'    => get_avatar_url( get_the_author_meta('user_email') ),
		'author_description' => get_the_author_meta('description'),
		'format'	=> $data->data['format'],
		'link'     	=> $data->data['link'],
		'thumbnail' => $thumbnail[3] ? $thumbnail[0] : "",
		'medium'    => $medium[3] ? $medium[0] : "",
		'mediumLarge'    => $mediumLarge[3] ? $mediumLarge[0] : "",
		'large'     => $large[3] ? $large[0] : "",
		'mobile'    => $mobile[3] ? $mobile[0] : "",
		'tablet'    => $tablet[3] ? $tablet[0] : "",
		'tabletPro'    => $tabletPro[3] ? $tabletPro[0] : "",
		'categories'  => $categoriesName,
		"topLevelCategories" => $top_level_categories,
		'tags'      => $tagNames,
		
	];
}

function testt(){
	return 'Hola enfermera';
}

function get_active_categories_for_app($data, $post, $context) {
	
	return [
		'id'		=> $data->data['id'],
		'count'     => $data->data['count'],
		'name'     => $data->data['name'],
		'cat_news_low_consume' => get_rest_url() . FA_ACTIVE_CATEGORIES  . '/'. $data->data['id'], 
		'cat_news' => get_rest_url(null, 'wp/v2/categories') . '/' . $data->data['id'],
	];
	
}

function get_envivo_info(){
	$data = array("upcoming_events" => array("now" => "Los Simpson", "next" => "Padre de Familia"), "banner" => "https://josuemoragonzalez.dx.am/wp-content/uploads/2019/09/300x250.jpg");
	
	$response = new WP_REST_Response($data);
	return $response;
}

function changeTokenExpiration(){
	return null;
}

function test_track_click_ad(){
	return json_encode("Hola esto es la respuesta del post");
}

/*function html_to_obj($html) {
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    return $this->element_to_obj($dom->documentElement);
}

function element_to_obj($element) {
    $obj = array( "tag" => $element->tagName );
    foreach ($element->attributes as $attribute) {
        $obj[$attribute->name] = $attribute->value;
    }
	$text = "";
    foreach ($element->childNodes as $subElement) {
		$counter = 1;
		$obj["nodeType"] = $subElement->nodeType;
        if ($subElement->nodeType == XML_TEXT_NODE) {
            $obj["html"][] =  $subElement->wholeText;
        }
        else {
            $obj["children"][] = $this->element_to_obj($subElement);
        }
    }
    return $obj;
}*/



}

?>