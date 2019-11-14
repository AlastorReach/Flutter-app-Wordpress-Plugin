<?php

if(! class_exists('AddCustomPostTypes')){
	class AddCustomPostTypes {
		
		public function __construct(){
			add_action( 'init', [ $this, 'fa_Add_Custom_Post_Type_Push_Notification' ] );
		}

		public function activate(){
			//Función que registra el nuevo Custom Post Type [fapushnotification]
			$this->fa_Add_Custom_Post_Type_Push_Notification();
		}
		
		function fa_Add_Custom_Post_Type_Push_Notification(){
			
			$post_type = "fapushnotification";
			
			$labels = [
				'name' 					=> __( 'Notificaciones Push' ),
				'singular_name' 		=> __( 'Notificación' ),
				'add_new'				=> __( 'Crear nuevo mensaje de notificación' ),
				'add_new item'			=> __( 'Crear nuevo mensaje de notificación' ),
				'search_items' 			=> __( 'Buscar mensaje de notificación' ),
				'edit_item'				=> __( 'Editar mensaje de notificación' ),
				'new_item' 				=> __( 'Notificación' ),
				'menu_name'				=> __( 'Notificaciones Push' ),
				//'all_items'          	=> __( 'Todas las notificaciones' ),
				'name_custom_bar'		=> __( 'Notificación' ),
				'not_found'           	=> __( 'No hay notificaciones' ),
				'not_found_in_trash'  	=> __( 'No hay notificaciones en el basurero' )
			];
			
			$args = [
				'labels'				=> $labels,
				'show_ui'				=> true,
				'show_in_menu'			=> false,
				'capability_type'		=> 'post',
				'hierarchical'			=> false,
				'public'				=> false,
				'has_archive'			=> false,
				'publicaly_querable'	=> false,
				'query_var'				=> false,
				//'menu_position'			=> 65,
				'supports'				=> array('title', 'excerpt', 'featured_image', 'thumbnail'),
				'show_in_menu' 			=> 'aplicacion_movil',
				//'taxonomies'			=> array( 'subscriptions' )
			];
			
			register_post_type( $post_type, $args );
		}
		
	}
	
	$addCustomPostTypes = new AddCustomPostTypes();
}

//activation
register_activation_hook( __FILE__, [ $addCustomPostTypes, 'activate' ] );

$newAddCustomPostTypes = new AddCustomPostTypes();
return $newAddCustomPostTypes;
?>
    

