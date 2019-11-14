<?php

class FA_Custom_Database_Tables {
	
	public function FA_create_tables() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'fa_listed_categories';

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL PRIMARY KEY,
			is_selected boolean,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		add_option( 'fa_db_version', FA()->version  );
	}
	
}
