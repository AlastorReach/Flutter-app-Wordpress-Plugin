<?php
class FA_Admin_Menus {
    
    
    public function __construct() {
        // Add menus.
        //add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
        add_action( 'admin_menu', array($this, 'my_plugin_menu') );
    }
    
    /**
     * Add menu items.
     */
    /*public function admin_menu() {
        global $menu;
        
        if ( current_user_can( 'manage_options' ) ) {
        add_menu_page('Flutter App', 'Flutter App', 'manage_options', 'flutterapp', null, null, '55.5' );*/
        
        /*add_submenu_page( 'edit.php?post_type=product', __( 'Attributes', 'woocommerce' ), __( 'Attributes', 'woocommerce' ), 'manage_product_terms', 'product_attributes', array( $this, 'attributes_page' ) );*/
       // }
    //}
    /** Step 1. */
    function my_plugin_menu() {
		
		/// edit.php?post_type=fapushnotification
		/// $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position
        add_menu_page( 'Aplicación Móvil', 'Aplicación Móvil', 'manage_options', 'aplicacion_movil', null, null, 100);
		
		//parent_slug, page_title, menu_title, capability, menu_slug, callback
		add_submenu_page( 'aplicacion_movil', 'Firebase', 'Ajustes', 'manage_options', 'aplicacion_movil', array( $this, 'fa_FirebasePage' ) );
		
		
        add_submenu_page( 'aplicacion_movil', 'Categorías', 'Categorías', 'manage_options', 'active_categories', array( $this, 'FA_Categories_Page' ) );
    }
    
    /** Step 3. */
    function my_plugin_options() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        echo '<div class="wrap">';
        echo '<p>Here is where the form would go if I actually had options.</p>';
        echo '</div>';
    }
    
    function FA_Categories_Page(){
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        if(isset($_POST['active_terms'])){
            foreach ($_POST['active_terms'] as $key => $value) {
               update_term_meta(intval($key), 'show_in_custom_rest', sanitize_text_field($value));
            }
			$option = get_option( 'flutter_categories_version');
			$option = intval($option) + 1;
			update_option( 'flutter_categories_version', strval($option), $autoload );
        }
        $argsCat = array(
        'parent' => 0,
		'hide_empty' => true,
		'taxonomy' => 'category'
        );
        $categories = get_categories($argsCat);
        if(count($categories) > 0){
            echo '<div class="card">';
                echo '<h3>Categorías</h3>';
                echo '<p> Active las categorías que deseas que aparezcan en el custom endpoint de WP Rest API <a href="'.get_home_url().'/wp-json/active_categories/v1/categories" target="_blank">link</a></p>';
                echo '<form method="POST">';
            echo '<div class="alastor-category-items">';
            for($i = 0; $i < count($categories); $i++){
                if(get_term_meta($categories[$i]->term_id, 'show_in_custom_rest', true) === "yes"){
                echo    '<div class="alastor-category_item">' .
                            '<label class="alastor-category_name">'.$categories[$i]->name. ' (' . $categories[$i]->count . ')</label>' .
                            '<label class="alastor-switch">'.
                            '<input type="hidden" name="active_terms['.$categories[$i]->term_id.']">'.
                            '<input type="checkbox" class="alastor-category-input" checked  >' .
                            '<span class="alastor-slider alastor-round"></span>' .
                        '</div>';
                    }
                    else if(get_term_meta($categories[$i]->term_id, 'show_in_custom_rest', true) === "no" || get_term_meta($categories[$i]->term_id, 'show_in_custom_rest', true) === "" ){
                        echo    '<div class="alastor-category_item">' .
                            '<label class="alastor-category_name">'.$categories[$i]->name. ' (' . $categories[$i]->count . ')</label>' .
                            '<label class="alastor-switch">'.
                            '<input type="hidden"  name="active_terms['.$categories[$i]->term_id.']">'.
                            '<input type="checkbox" class="alastor-category-input">' .
                            '<span class="alastor-slider alastor-round"></span>' .
                        '</div>';
                    }
                
            }
            echo '<div class="alastor-btn-container"><input id="alastor-btn-submit-active-categories" type="submit" class="alastor-btn" value="Actualizar"></div>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            ?>
            <script>
                (function(){
                    var alastorBtnSudmit = document.getElementById("alastor-btn-submit-active-categories");
                   alastorBtnSudmit.addEventListener("click", fnAlastorHandleSubmitForm, false);

                    function fnAlastorHandleSubmitForm(e){
                        //e.preventDefault();
                        let formInputs = document.getElementsByClassName("alastor-category-input");
                        for(let i = 0; i < formInputs.length; i++){
                            if(formInputs[i].checked){
                                formInputs[i].value = "yes";
                            }
                            else{
                                formInputs[i].value = "no"; 
                            }
                            formInputs[i].previousSibling.value = formInputs[i].value;
                        }
                    }
                })();
            </script>
            <?php
        }
        
    }
	
	function fa_FirebasePage(){
		
		if($_SERVER['REQUEST_METHOD'] === 'POST'){
			if(isset($_POST['flutterapp_settings'])){
				foreach($_POST['flutterapp_settings'] as $key => $value){
					switch($key){
						case 'FA_FIREBASE_API_KEY': update_option('flutter_firebase_api_key', $value);break;
						case 'SEND_NOTIFICATION_WHEN_A_POST_IS_PUBLISHED': update_option('flutter_send_push_notifications', $value === "true" ? true : false);break;
						case 'TOTAL_AMOUNT_OF_NEWS_IN_DATABASE_PER_CATEGORY': update_option('flutter_total_amount_of_news_stored_in_database_per_category', intval($value));break;
						case 'TOTAL_AMOUNT_OF_NEWS_FOR_EACH_REQUEST_PER_CATEGORY': update_option('flutter_total_amount_of_news_per_request', intval($value));break;
						case 'TOTAL_AMOUNT_OF_PAGES_PER_CATEGORY': update_option('flutter_max_number_of_pages_on_api_request', intval($value));break;
						case 'FA_EN_VIVO_URL': update_option('flutter_envivo_url', $value);break;
						case 'FA_EMAIL_FOR_NOTIFICATIONS': update_option('flutter_email_for_notifications', $value);break;
					}
				}
			}
			
		}
		
		$firebase_api_key = get_option('flutter_firebase_api_key');
		$email_for_notifications = get_option('flutter_email_for_notifications');
		$send_push_notifications = get_option('flutter_send_push_notifications');
		$amount_of_news_stored_in_database_per_category = get_option('flutter_total_amount_of_news_stored_in_database_per_category');
		$amount_of_news_per_request = get_option('flutter_total_amount_of_news_per_request');
		$max_number_of_pages_on_api_request = get_option('flutter_max_number_of_pages_on_api_request');
		$envivo_url = get_option('flutter_envivo_url');
		
		$checked = ($send_push_notifications) ? "checked='checked'" : "";
		
		echo '<style> .form_api_key .form-table th{ vertical-align: middle!important} </style>';
		echo '<form class="form_api_key" method="POST">' .
				'<table class="form-table">' .
					'<tbody>' .
						'<tr>' .
							'<th>Clave de API web de Firebase</th>' .
							'<td>' .
								'<table class="widefat">' .
									'<tbody>' .
										'<tr>' .
											'<td>' .
												'<input type="text" style="width:100%" name="flutterapp_settings[FA_FIREBASE_API_KEY]" value="'.$firebase_api_key.'">' .
											'</td>' .
										'</tr>' .
									'</tbody>' .
								'</table>' .
							'</td>' .
						'</tr>' .
						'<tr>' .
							'<th>Email para alertar sobre notificaciones del comportamiento de FlutterApp</th>' .
							'<td>' .
								'<table class="widefat">' .
									'<tbody>' .
										'<tr>' .
											'<td>' .
												'<input type="text" style="width:100%" name="flutterapp_settings[FA_EMAIL_FOR_NOTIFICATIONS]" value="'.$email_for_notifications.'">' .
											'</td>' .
										'</tr>' .
									'</tbody>' .
								'</table>' .
							'</td>' .
						'</tr>' .
						'<tr>' .
							'<th>URL de Streaming en vivo</th>' .
							'<td>' .
								'<table class="widefat">' .
									'<tbody>' .
										'<tr>' .
											'<td>' .
												'<input type="text" style="width:100%" name="flutterapp_settings[FA_EN_VIVO_URL]" value="'.$envivo_url.'">' .
											'</td>' .
										'</tr>' .
									'</tbody>' .
								'</table>' .
							'</td>' .
						'</tr>' .
						'<tr>' .
							'<th>¿Enviar notificación al crear un nuevo post?</th>' .
							'<td>' .
								'<table class="widefat">' .
									'<tbody>' .
										'<tr>' .
											'<td>' .
												'<input type="hidden" name="flutterapp_settings[SEND_NOTIFICATION_WHEN_A_POST_IS_PUBLISHED]">' .
												'<input type="checkbox" class="check_input" id="flutter_app_send_notifications" value="true" '. $checked.'> Valor por defecto [true]' .
											'</td>' .
										'</tr>' .
									'</tbody>' .
								'</table>' .
							'</td>' .
						'</tr>' .
						'<tr>' .
							'<th>Cantidad de noticias que se guardan en la base de datos por categoría</th>' .
							'<td>' .
								'<table class="widefat">' .
									'<tbody>' .
										'<tr>' .
											'<td>' .
												'<input type="number" min="5" max="30" value="'.$amount_of_news_stored_in_database_per_category.'"  name="flutterapp_settings[TOTAL_AMOUNT_OF_NEWS_IN_DATABASE_PER_CATEGORY]"> Valor por defecto [10]' .
											'</td>' .
										'</tr>' .
									'</tbody>' .
								'</table>' .
							'</td>' .
						'</tr>' .
						'<tr>' .
							'<th>Cantidad de noticias que se reciben por cada petición al servidor</th>' .
							'<td>' .
								'<table class="widefat">' .
									'<tbody>' .
										'<tr>' .
											'<td>' .
												'<input type="number" min="5" max="30" value="'.$amount_of_news_per_request.'"  name="flutterapp_settings[TOTAL_AMOUNT_OF_NEWS_FOR_EACH_REQUEST_PER_CATEGORY]"> Valor por defecto [10]' .
											'</td>' .
										'</tr>' .
									'</tbody>' .
								'</table>' .
							'</td>' .
						'</tr>' .
						'<tr>' .
							'<th>Cantidad de máxima de páginas de noticias por categoría</th>' .
							'<td>' .
								'<table class="widefat">' .
									'<tbody>' .
										'<tr>' .
											'<td>' .
												'<input type="number" min="1" max="5" value="'.$max_number_of_pages_on_api_request.'"  name="flutterapp_settings[TOTAL_AMOUNT_OF_PAGES_PER_CATEGORY]"> Valor por defecto [5]' .
											'</td>' .
										'</tr>' .
									'</tbody>' .
								'</table>' .
							'</td>' .
						'</tr>' .
					'</tbody>' .
				'</table>' .
				'<div class="alastor-btn-container"><input id="alastor-btn-submit-flutterapp_settings" type="submit" class="alastor-btn" value="Actualizar"></div>' .
			'</form>';
			
			echo '<script>
                (function(){
                    var alastorBtnSudmit = document.getElementById("alastor-btn-submit-flutterapp_settings");
                   alastorBtnSudmit.addEventListener("click", fnAlastorHandleSubmitForm, false);

                    function fnAlastorHandleSubmitForm(e){
                        //e.preventDefault();
                        let formInputs = document.getElementsByClassName("check_input");
                        for(let i = 0; i < formInputs.length; i++){
                            if(formInputs[i].checked){
                                formInputs[i].value = "true";
                            }
                            else{
                                formInputs[i].value = "false"; 
                            }
                            formInputs[i].previousSibling.value = formInputs[i].value;
                        }
                    }
                })();
            </script>';
											
	}
	
	function fa_Send_Notification() {
		
	}
}
    return new FA_Admin_Menus();
?>
