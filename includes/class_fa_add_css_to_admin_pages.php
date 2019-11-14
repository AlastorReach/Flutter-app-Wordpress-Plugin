<?php

if(! class_exists('AddCSSToAdminPages')){
	class AddCSSToAdminPages {
		
		public function __construct(){
			add_action('admin_enqueue_scripts', array($this,'admin_style'));
		}
		
		// Update CSS within in Admin
		function admin_style() {
			wp_enqueue_style('admin-styles', FA_PLUGIN_ROOT. 'css/admin.css');
		}
	}
}
return new AddCSSToAdminPages();
?>
    

